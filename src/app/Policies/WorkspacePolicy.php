<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    /**
     * Determine if the user can view the workspace
     */
    public function view(User $user, Workspace $workspace): bool
    {
        // Owner can always view
        if ($user->id === $workspace->user_id) {
            return true;
        }

        // Shared users can view if they have access
        return $workspace->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine if the user can create a workspace
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the workspace
     */
    public function update(User $user, Workspace $workspace): bool
    {
        // Only owner and editors can update
        if ($user->id === $workspace->user_id) {
            return true;
        }

        $role = $workspace->users()
            ->where('user_id', $user->id)
            ->pluck('role')
            ->first();

        return $role === 'editor';
    }

    /**
     * Determine if the user can delete the workspace
     */
    public function delete(User $user, Workspace $workspace): bool
    {
        // Only owner can delete
        return $user->id === $workspace->user_id;
    }
}
