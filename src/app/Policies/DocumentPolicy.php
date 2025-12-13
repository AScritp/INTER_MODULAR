<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Models\Workspace;

class DocumentPolicy
{
    /**
     * Determine if the user can view the document
     */
    public function view(User $user, Document $document): bool
    {
        $workspace = $document->workspace;

        // Owner of workspace can view
        if ($user->id === $workspace->user_id) {
            return true;
        }

        // Creator can view
        if ($user->id === $document->user_id) {
            return true;
        }

        // Shared users with any role can view
        return $workspace->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine if the user can create a document
     */
    public function create(User $user, Workspace $workspace): bool
    {
        // Only owner and editors can create
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
     * Determine if the user can update the document
     */
    public function update(User $user, Document $document): bool
    {
        $workspace = $document->workspace;

        // Owner can update
        if ($user->id === $workspace->user_id) {
            return true;
        }

        // Creator can update
        if ($user->id === $document->user_id) {
            return true;
        }

        // Only editors can update
        $role = $workspace->users()
            ->where('user_id', $user->id)
            ->pluck('role')
            ->first();

        return $role === 'editor';
    }

    /**
     * Determine if the user can delete the document
     */
    public function delete(User $user, Document $document): bool
    {
        $workspace = $document->workspace;

        // Only workspace owner can delete
        if ($user->id === $workspace->user_id) {
            return true;
        }

        // Only creators can delete their own
        return $user->id === $document->user_id;
    }
}
