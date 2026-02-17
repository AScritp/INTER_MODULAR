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
        if ($user->id === $workspace->user_id) {
            return true;
        }
        $shared = $workspace->users()->where('user_id', $user->id)->first();
        if (!$shared) {
            return false;
        }
        $perm = $shared->pivot->permissions ?? null;
        if (is_array($perm)) {
            return !empty($perm['read_workspace']);
        }
        return true;
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
        if ($user->id === $workspace->user_id) {
            return true;
        }
        $shared = $workspace->users()->where('user_id', $user->id)->first();
        if (!$shared) {
            return false;
        }
        $perm = $shared->pivot->permissions ?? null;
        if (is_array($perm)) {
            return !empty($perm['update_workspace']);
        }
        return ($shared->pivot->role ?? null) === 'editor';
    }

    /**
     * Determine if the user can delete the workspace
     */
    public function delete(User $user, Workspace $workspace): bool
    {
        if ($user->id === $workspace->user_id) {
            return true;
        }
        $shared = $workspace->users()->where('user_id', $user->id)->first();
        if (!$shared) {
            return false;
        }
        $perm = $shared->pivot->permissions ?? null;
        if (is_array($perm)) {
            return !empty($perm['delete_workspace']);
        }
        return false;
    }
}
