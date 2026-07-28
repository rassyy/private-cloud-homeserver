<?php

namespace App\Policies;

use App\Models\FileShare;
use App\Models\Folder;
use App\Models\User;

class FolderPolicy
{
    public function view(User $user, Folder $folder): bool
    {
        return $this->hasAccess($user, $folder);
    }

    public function update(User $user, Folder $folder): bool
    {
        return $this->hasAccess($user, $folder, 'edit');
    }

    public function delete(User $user, Folder $folder): bool
    {
        return $this->hasAccess($user, $folder, 'edit');
    }

    private function hasAccess(User $user, Folder $folder, ?string $requiredPermission = null): bool
    {
        if ($user->id === $folder->user_id) {
            return true;
        }

        $query = FileShare::where('shareable_type', $folder->getMorphClass())
            ->where('shareable_id', $folder->id)
            ->where('user_id', $user->id);

        if ($requiredPermission === 'edit') {
            $query->where('permission', 'edit');
        }

        if ($query->exists()) {
            return true;
        }

        $parentId = $folder->parent_id;
        while ($parentId) {
            $parentQuery = FileShare::where('shareable_type', $folder->getMorphClass())
                ->where('shareable_id', $parentId)
                ->where('user_id', $user->id);

            if ($requiredPermission === 'edit') {
                $parentQuery->where('permission', 'edit');
            }

            if ($parentQuery->exists()) {
                return true;
            }

            $parentId = Folder::where('id', $parentId)->value('parent_id');
        }

        return false;
    }
}
