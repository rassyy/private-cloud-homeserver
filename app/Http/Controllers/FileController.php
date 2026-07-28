<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileShare;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mime\MimeTypes;

class FileController extends Controller
{
    use AuthorizesRequests;

    /**
     * Upload one or more files (supports both chunked and multi-file array uploads).
     */
    public function store(Request $request)
    {
        $userId = Auth::id();
        $folderId = $request->folder_id && $request->folder_id !== 'null' ? (int) $request->folder_id : null;

        if (!file_exists(storage_path('app/uploads'))) {
            @mkdir(storage_path('app/uploads'), 0755, true);
        }
        if (!file_exists(storage_path('app/chunks'))) {
            @mkdir(storage_path('app/chunks'), 0755, true);
        }
        if (!file_exists(storage_path('app/private/chunks'))) {
            @mkdir(storage_path('app/private/chunks'), 0755, true);
        }

        if ($folderId) {
            $folder = Folder::findOrFail($folderId);
            $this->authorize('view', $folder);
        }

        // Handle chunked upload via Pion / Resumable.js
        if ($request->has('resumableIdentifier') || $request->hasFile('file')) {
            $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

            if ($receiver->isUploaded()) {
                $save = $receiver->receive();

                if ($save->isFinished()) {
                    $uploadedFile = $save->getFile();
                    $fileRecord = $this->saveUploadedFile($uploadedFile, $folderId, $userId, $request->input('resumableFilename'));
                    @unlink($uploadedFile->getPathname());

                    return response()->json([
                        'status' => 'success',
                        'file' => [
                            'id' => $fileRecord->id,
                            'name' => $fileRecord->name,
                            'size' => $fileRecord->formatted_size,
                        ],
                    ]);
                }

                $handler = $save->handler();
                return response()->json([
                    'done' => $handler->getPercentageDone(),
                    'status' => true,
                ]);
            }
        }

        // Handle standard array upload ('files[]')
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $uploadedFile) {
                $this->saveUploadedFile($uploadedFile, $folderId, $userId);
            }
        }

        return back();
    }

    /**
     * Download a file.
     */
    public function download(File $file)
    {
        $this->authorize('view', $file);

        $file->update(['last_accessed_at' => now()]);

        $downloadName = $file->name;
        if (!pathinfo($downloadName, PATHINFO_EXTENSION) && $file->mime_type) {
            $extensions = MimeTypes::getDefault()->getExtensions($file->mime_type);
            if (!empty($extensions)) {
                $downloadName .= '.' . $extensions[0];
            }
        }

        return response()->download(
            Storage::disk('uploads')->path($file->storage_path),
            $downloadName,
            ['Content-Type' => $file->mime_type]
        );
    }

    /**
     * Get file details (for the details panel).
     */
    public function show(File $file)
    {
        $this->authorize('view', $file);

        $file->update(['last_accessed_at' => now()]);

        $folder = $file->folder;
        $locationParts = ['My Files'];
        if ($folder) {
            $ancestors = $folder->ancestors;
            foreach ($ancestors as $a) {
                $locationParts[] = $a->name;
            }
            $locationParts[] = $folder->name;
        }

        return response()->json([
            'id' => $file->id,
            'uuid' => $file->uuid,
            'name' => $file->name,
            'mime_type' => $file->mime_type,
            'extension' => $file->extension,
            'size' => $file->formatted_size,
            'size_bytes' => $file->size,
            'icon' => $file->icon,
            'icon_color' => $file->icon_color,
            'is_starred' => $file->is_starred,
            'location' => implode(' / ', $locationParts),
            'owner' => $file->user->name,
            'created_at' => $file->created_at->format('M d, Y \a\t g:i A'),
            'updated_at' => $file->updated_at->format('M d, Y \a\t g:i A'),
            'last_accessed_at' => $file->last_accessed_at?->format('M d, Y \a\t g:i A'),
        ]);
    }

    /**
     * Rename a file.
     */
    public function update(Request $request, File $file)
    {
        $this->authorize('update', $file);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $file->update(['name' => $validated['name']]);

        return back();
    }

    /**
     * Soft-delete (trash) a file.
     */
    public function destroy(File $file)
    {
        $this->authorize('delete', $file);

        $file->update(['trashed_at' => now()]);

        return back();
    }

    /**
     * Toggle star status.
     */
    public function toggleStar(File $file)
    {
        $this->authorize('update', $file);

        $file->update(['is_starred' => !$file->is_starred]);

        return back();
    }

    /**
     * Share a file with another registered user.
     */
    public function share(Request $request, File $file)
    {
        $this->authorize('update', $file);

        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'permission' => ['nullable', 'string', 'in:view,edit'],
        ]);

        if ($request->email === Auth::user()->email) {
            return back()->withErrors(['email' => 'You cannot share a file with yourself.']);
        }

        $targetUser = User::where('email', $request->email)->firstOrFail();

        FileShare::updateOrCreate([
            'shareable_type' => $file->getMorphClass(),
            'shareable_id' => $file->id,
            'user_id' => $targetUser->id,
        ], [
            'created_by' => Auth::id(),
            'permission' => $request->input('permission', 'view'),
        ]);

        return back()->with('success', 'File shared successfully with ' . $targetUser->name);
    }

    /**
     * Move a file to a different folder.
     */
    public function move(Request $request, File $file)
    {
        $this->authorize('update', $file);

        $validated = $request->validate([
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        if (!empty($validated['folder_id'])) {
            $folder = Folder::findOrFail($validated['folder_id']);
            $this->authorize('view', $folder);
        }

        $file->update(['folder_id' => $validated['folder_id'] ?? null]);

        return back();
    }

    /**
     * Restore a trashed file.
     */
    public function restore($id)
    {
        $file = File::where('user_id', Auth::id())->whereNotNull('trashed_at')->findOrFail($id);
        $file->update(['trashed_at' => null]);

        return back();
    }

    /**
     * Force delete a file permanently.
     */
    public function forceDelete($id)
    {
        $file = File::where('user_id', Auth::id())->whereNotNull('trashed_at')->findOrFail($id);
        Storage::disk('uploads')->delete($file->storage_path);
        $file->delete();

        return back();
    }

    /**
     * Serve a file for preview (images, PDFs, etc.)
     */
    public function preview(File $file)
    {
        $this->authorize('view', $file);

        return response()->file(
            Storage::disk('uploads')->path($file->storage_path),
            ['Content-Type' => $file->mime_type]
        );
    }

    // ── Private Helpers ──

    private function saveUploadedFile($uploadedFile, ?int $folderId, int $userId, ?string $fallbackName = null): File
    {
        $originalName = $fallbackName ?: (method_exists($uploadedFile, 'getClientOriginalName') ? $uploadedFile->getClientOriginalName() : $uploadedFile->getFilename());
        $extension = method_exists($uploadedFile, 'getClientOriginalExtension') ? $uploadedFile->getClientOriginalExtension() : pathinfo($originalName, PATHINFO_EXTENSION);
        if (!$extension) {
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        }
        $mimeType = method_exists($uploadedFile, 'getMimeType') ? $uploadedFile->getMimeType() : 'application/octet-stream';
        $size = $uploadedFile->getSize();

        $finalName = $this->resolveFilename($originalName, $folderId, $userId);

        $uuid = Str::uuid()->toString();
        $fileName = $extension ? "{$uuid}.{$extension}" : $uuid;
        $storagePath = "{$userId}/{$fileName}";

        $uploadDir = storage_path("app/uploads/{$userId}");
        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $stream = fopen($uploadedFile->getPathname(), 'r');
        Storage::disk('uploads')->put($storagePath, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        return File::create([
            'uuid' => $uuid,
            'user_id' => $userId,
            'folder_id' => $folderId,
            'name' => $finalName,
            'storage_path' => $storagePath,
            'mime_type' => $mimeType,
            'size' => $size,
            'extension' => $extension,
        ]);
    }

    private function resolveFilename(string $originalName, ?int $folderId, int $userId): string
    {
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);

        $existingNames = File::where('user_id', $userId)
            ->where('folder_id', $folderId)
            ->notTrashed()
            ->pluck('name')
            ->toArray();

        if (!in_array($originalName, $existingNames)) {
            return $originalName;
        }

        $counter = 1;
        do {
            $candidate = $ext ? "{$name}({$counter}).{$ext}" : "{$name}({$counter})";
            $counter++;
        } while (in_array($candidate, $existingNames));

        return $candidate;
    }
}
