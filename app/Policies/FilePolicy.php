<?php

namespace App\Policies;

use App\Models\File;
use App\Models\FileShare;
use App\Models\Folder;
use App\Models\User;

class FilePolicy
{
    public function view(User $user, File $file): bool
    {
        return $this->hasAccess($user, $file);
    }

    public function update(User $user, File $file): bool
    {
        return $this->hasAccess($user, $file, 'edit');
    }

    public function delete(User $user, File $file): bool
    {
        return $this->hasAccess($user, $file, 'edit');
    }

    private function hasAccess(User $user, File $file, ?string $requiredPermission = null): bool
    {
        if ($user->id === $file->user_id) {
            return true;
        }

        $query = FileShare::where('shareable_type', $file->getMorphClass())
            ->where('shareable_id', $file->id)
            ->where('user_id', $user->id);

        if ($requiredPermission === 'edit') {
            $query->where('permission', 'edit');
        }

        if ($query->exists()) {
            return true;
        }

        $folderId = $file->folder_id;
        while ($folderId) {
            $folderQuery = FileShare::where('shareable_type', (new Folder)->getMorphClass())
                ->where('shareable_id', $folderId)
                ->where('user_id', $user->id);

            if ($requiredPermission === 'edit') {
                $folderQuery->where('permission', 'edit');
            }

            if ($folderQuery->exists()) {
                return true;
            }

            $folderId = Folder::where('id', $folderId)->value('parent_id');
        }

        return false;
    }
}
