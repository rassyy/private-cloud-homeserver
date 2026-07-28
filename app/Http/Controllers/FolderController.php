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
use Inertia\Inertia;
use Inertia\Response;

class FolderController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the root "My Files" view (no parent folder) or search results.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $search = $request->input('search');

        $foldersQuery = Folder::where('user_id', $user->id)->notTrashed();
        $filesQuery = File::where('user_id', $user->id)->notTrashed();

        if ($search) {
            $foldersQuery->where('name', 'like', "%{$search}%");
            $filesQuery->where('name', 'like', "%{$search}%");
        } else {
            $foldersQuery->root();
            $filesQuery->whereNull('folder_id');
        }

        $folders = $foldersQuery->orderBy('name')
            ->get()
            ->map(fn(Folder $f) => $this->formatFolder($f));

        $files = $filesQuery->orderBy('name')
            ->get()
            ->map(fn(File $f) => $this->formatFile($f));

        return Inertia::render('FileBrowser', [
            'folder' => $search ? ['name' => 'Search: "' . $search . '"', 'id' => null] : null,
            'folders' => $folders,
            'files' => $files,
            'ancestors' => [],
            'all_folders' => $this->getAllFolders($user->id),
            'view_mode' => $search ? 'search' : 'normal',
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Display a specific folder's contents.
     */
    public function show(Folder $folder): Response
    {
        $this->authorize('view', $folder);

        $user = Auth::user();

        $subfolders = Folder::where('user_id', $folder->user_id)
            ->where('parent_id', $folder->id)
            ->notTrashed()
            ->orderBy('name')
            ->get()
            ->map(fn(Folder $f) => $this->formatFolder($f));

        $files = File::where('user_id', $folder->user_id)
            ->where('folder_id', $folder->id)
            ->notTrashed()
            ->orderBy('name')
            ->get()
            ->map(fn(File $f) => $this->formatFile($f));

        $ancestors = collect($folder->ancestors)->map(fn(Folder $a) => [
            'id' => $a->id,
            'name' => $a->name,
        ])->values()->toArray();

        return Inertia::render('FileBrowser', [
            'folder' => [
                'id' => $folder->id,
                'uuid' => $folder->uuid,
                'name' => $folder->name,
                'is_starred' => $folder->is_starred,
            ],
            'folders' => $subfolders,
            'files' => $files,
            'ancestors' => $ancestors,
            'all_folders' => $this->getAllFolders($user->id),
            'view_mode' => 'normal',
        ]);
    }

    /**
     * Display starred folders and files.
     */
    public function starred(): Response
    {
        $user = Auth::user();

        $folders = Folder::where('user_id', $user->id)
            ->where('is_starred', true)
            ->notTrashed()
            ->orderBy('name')
            ->get()
            ->map(fn(Folder $f) => $this->formatFolder($f));

        $files = File::where('user_id', $user->id)
            ->where('is_starred', true)
            ->notTrashed()
            ->orderBy('name')
            ->get()
            ->map(fn(File $f) => $this->formatFile($f));

        return Inertia::render('FileBrowser', [
            'folder' => ['name' => 'Starred', 'id' => null],
            'folders' => $folders,
            'files' => $files,
            'ancestors' => [],
            'all_folders' => $this->getAllFolders($user->id),
            'view_mode' => 'starred',
        ]);
    }

    /**
     * Display recently modified files.
     */
    public function recent(): Response
    {
        $user = Auth::user();

        $files = File::where('user_id', $user->id)
            ->notTrashed()
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn(File $f) => $this->formatFile($f));

        return Inertia::render('FileBrowser', [
            'folder' => ['name' => 'Recent Files', 'id' => null],
            'folders' => [],
            'files' => $files,
            'ancestors' => [],
            'all_folders' => $this->getAllFolders($user->id),
            'view_mode' => 'recent',
        ]);
    }

    /**
     * Display shared items.
     */
    public function shared(): Response
    {
        $user = Auth::user();

        $shares = FileShare::with(['shareable', 'creator'])->where('user_id', $user->id)->get();

        $folders = [];
        $files = [];

        foreach ($shares as $share) {
            $item = $share->shareable;
            if (!$item || $item->trashed_at) {
                continue;
            }

            if ($item instanceof Folder) {
                $formatted = $this->formatFolder($item);
                $formatted['owner_name'] = $share->creator?->name ?? 'Unknown';
                $folders[] = $formatted;
            } elseif ($item instanceof File) {
                $formatted = $this->formatFile($item);
                $formatted['owner_name'] = $share->creator?->name ?? 'Unknown';
                $files[] = $formatted;
            }
        }

        return Inertia::render('FileBrowser', [
            'folder' => ['name' => 'Shared with me', 'id' => null],
            'folders' => $folders,
            'files' => $files,
            'ancestors' => [],
            'all_folders' => $this->getAllFolders($user->id),
            'view_mode' => 'shared',
        ]);
    }

    /**
     * Display items in Trash.
     */
    public function trash(): Response
    {
        $user = Auth::user();

        $folders = Folder::where('user_id', $user->id)
            ->whereNotNull('trashed_at')
            ->orderBy('trashed_at', 'desc')
            ->get()
            ->map(fn(Folder $f) => $this->formatFolder($f));

        $files = File::where('user_id', $user->id)
            ->whereNotNull('trashed_at')
            ->orderBy('trashed_at', 'desc')
            ->get()
            ->map(fn(File $f) => $this->formatFile($f));

        return Inertia::render('FileBrowser', [
            'folder' => ['name' => 'Trash', 'id' => null],
            'folders' => $folders,
            'files' => $files,
            'ancestors' => [],
            'all_folders' => $this->getAllFolders($user->id),
            'view_mode' => 'trash',
        ]);
    }

    /**
     * Create a new folder.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        if (!empty($validated['parent_id'])) {
            $parent = Folder::findOrFail($validated['parent_id']);
            $this->authorize('view', $parent);
        }

        Folder::create([
            'user_id' => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
        ]);

        return back();
    }

    /**
     * Rename a folder.
     */
    public function update(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $folder->update(['name' => $validated['name']]);

        return back();
    }

    /**
     * Soft-delete (trash) a folder.
     */
    public function destroy(Folder $folder)
    {
        $this->authorize('delete', $folder);

        $parentId = $folder->parent_id;
        $folderId = $folder->id;

        $folder->update(['trashed_at' => now()]);
        $this->trashDescendants($folder);

        if (str_contains(request()->header('referer', ''), "/folders/{$folderId}")) {
            return $parentId ? redirect()->route('folders.show', $parentId) : redirect()->route('folders.index');
        }

        return back();
    }

    /**
     * Toggle star status.
     */
    public function toggleStar(Folder $folder)
    {
        $this->authorize('update', $folder);

        $folder->update(['is_starred' => !$folder->is_starred]);

        return back();
    }

    /**
     * Share a folder with another registered user.
     */
    public function share(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'permission' => ['nullable', 'string', 'in:view,edit'],
        ]);

        if ($request->email === Auth::user()->email) {
            return back()->withErrors(['email' => 'You cannot share a folder with yourself.']);
        }

        $targetUser = User::where('email', $request->email)->firstOrFail();

        FileShare::updateOrCreate([
            'shareable_type' => $folder->getMorphClass(),
            'shareable_id' => $folder->id,
            'user_id' => $targetUser->id,
        ], [
            'created_by' => Auth::id(),
            'permission' => $request->input('permission', 'view'),
        ]);

        return back()->with('success', 'Folder shared successfully with ' . $targetUser->name);
    }

    /**
     * Move a folder to a new parent.
     */
    public function move(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        $validated = $request->validate([
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        if (!empty($validated['parent_id'])) {
            $target = Folder::findOrFail($validated['parent_id']);
            $this->authorize('view', $target);

            $current = $target;
            while ($current) {
                if ($current->id === $folder->id) {
                    return back()->withErrors(['parent_id' => 'Cannot move a folder into itself.']);
                }
                $current = $current->parent;
            }
        }

        $folder->update(['parent_id' => $validated['parent_id'] ?? null]);

        return back();
    }

    /**
     * Restore a trashed folder.
     */
    public function restore($id)
    {
        $folder = Folder::where('user_id', Auth::id())->whereNotNull('trashed_at')->findOrFail($id);
        $folder->update(['trashed_at' => null]);
        $this->restoreDescendants($folder);

        return back();
    }

    /**
     * Force delete a folder permanently.
     */
    public function forceDelete($id)
    {
        $folder = Folder::where('user_id', Auth::id())->whereNotNull('trashed_at')->findOrFail($id);
        $this->forceDeleteFolderAndDescendants($folder);

        return back();
    }

    /**
     * Empty the entire trash for user.
     */
    public function emptyTrash()
    {
        $userId = Auth::id();

        $trashedFiles = File::where('user_id', $userId)->whereNotNull('trashed_at')->get();
        foreach ($trashedFiles as $file) {
            Storage::disk('uploads')->delete($file->storage_path);
            $file->delete();
        }

        $trashedFolders = Folder::where('user_id', $userId)->whereNotNull('trashed_at')->get();
        foreach ($trashedFolders as $f) {
            $f->delete();
        }

        return back();
    }

    // ── Private Helpers ──

    private function formatFolder(Folder $folder): array
    {
        return [
            'id' => $folder->id,
            'uuid' => $folder->uuid,
            'name' => $folder->name,
            'is_starred' => $folder->is_starred,
            'items_count' => $folder->children()->notTrashed()->count() + $folder->files()->notTrashed()->count(),
            'updated_at' => $folder->updated_at->diffForHumans(),
            'type' => 'folder',
        ];
    }

    private function formatFile(File $file): array
    {
        return [
            'id' => $file->id,
            'uuid' => $file->uuid,
            'name' => $file->name,
            'mime_type' => $file->mime_type,
            'size' => $file->formatted_size,
            'size_bytes' => $file->size,
            'extension' => $file->extension,
            'icon' => $file->icon,
            'icon_color' => $file->icon_color,
            'is_starred' => $file->is_starred,
            'updated_at' => $file->updated_at->diffForHumans(),
            'created_at' => $file->created_at->format('M d, Y'),
            'type' => 'file',
        ];
    }

    private function getAllFolders(int $userId): array
    {
        return Folder::where('user_id', $userId)
            ->notTrashed()
            ->orderBy('name')
            ->get()
            ->map(fn(Folder $f) => [
                'id' => $f->id,
                'parent_id' => $f->parent_id,
                'name' => $f->name,
            ])->toArray();
    }

    private function trashDescendants(Folder $folder): void
    {
        File::where('folder_id', $folder->id)->update(['trashed_at' => now()]);

        $children = Folder::where('parent_id', $folder->id)->get();
        foreach ($children as $child) {
            $child->update(['trashed_at' => now()]);
            $this->trashDescendants($child);
        }
    }

    private function restoreDescendants(Folder $folder): void
    {
        File::where('folder_id', $folder->id)->whereNotNull('trashed_at')->update(['trashed_at' => null]);

        $children = Folder::where('parent_id', $folder->id)->whereNotNull('trashed_at')->get();
        foreach ($children as $child) {
            $child->update(['trashed_at' => null]);
            $this->restoreDescendants($child);
        }
    }

    private function forceDeleteFolderAndDescendants(Folder $folder): void
    {
        $files = File::where('folder_id', $folder->id)->get();
        foreach ($files as $file) {
            Storage::disk('uploads')->delete($file->storage_path);
            $file->delete();
        }

        $children = Folder::where('parent_id', $folder->id)->get();
        foreach ($children as $child) {
            $this->forceDeleteFolderAndDescendants($child);
        }

        $folder->delete();
    }
}
