<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Models\Workspace;
use App\Models\ResourceShare;

class DocumentPolicy
{
    /**
     * Determine if the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        $workspace = $document->workspace;

        if ($workspace->user_id === $user->id) {
            return true;
        }

        $shared = $workspace->users()->where('user_id', $user->id)->first();
        if ($shared) {
            $perm = $shared->pivot->permissions ?? null;
            if (is_array($perm)) {
                if (!empty($perm['read_existing_docs'])) {
                    return true;
                }
                if (!empty($perm['read_own_docs']) && $document->user_id === $user->id) {
                    return true;
                }
            }
            return true;
        }

        $resourceShare = ResourceShare::where('resource_type', 'document')
            ->where('resource_id', $document->id)
            ->where('user_id', $user->id)
            ->first();
        if ($resourceShare) {
            $rp = $resourceShare->permissions ?? [];
            return !empty($rp['read']);
        }

        return false;
    }

    /**
     * Determine if the user can create documents.
     */
    public function create(User $user, Workspace $workspace): bool
    {
        if ($workspace->user_id === $user->id) {
            return true;
        }
        $shared = $workspace->users()->where('user_id', $user->id)->first();
        if (!$shared) {
            return false;
        }
        $perm = $shared->pivot->permissions ?? null;
        if (is_array($perm)) {
            return !empty($perm['create_doc']);
        }
        return ($shared->pivot->role ?? null) === 'editor';
    }

    /**
     * Determine if the user can update the document.
     */
    public function update(User $user, Document $document): bool
    {
        $workspace = $document->workspace;
        if ($workspace->user_id === $user->id) {
            return true;
        }
        $shared = $workspace->users()->where('user_id', $user->id)->first();
        if ($shared) {
            $perm = $shared->pivot->permissions ?? null;
            if (is_array($perm)) {
                if (!empty($perm['update_any_doc'])) {
                    return true;
                }
                if (!empty($perm['update_own_doc']) && $document->user_id === $user->id) {
                    return true;
                }
            }
            if (($shared->pivot->role ?? null) === 'editor') {
                return true;
            }
        }
        $resourceShare = ResourceShare::where('resource_type', 'document')
            ->where('resource_id', $document->id)
            ->where('user_id', $user->id)
            ->first();
        if ($resourceShare) {
            $rp = $resourceShare->permissions ?? [];
            return !empty($rp['update']);
        }
        return false;
    }

    /**
     * Determine if the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        $workspace = $document->workspace;
        if ($workspace->user_id === $user->id) {
            return true;
        }
        $shared = $workspace->users()->where('user_id', $user->id)->first();
        if ($shared) {
            $perm = $shared->pivot->permissions ?? null;
            if (is_array($perm)) {
                if (!empty($perm['delete_any_doc'])) {
                    return true;
                }
                if (!empty($perm['delete_own_doc']) && $document->user_id === $user->id) {
                    return true;
                }
            }
            if (($shared->pivot->role ?? null) === 'editor') {
                return true;
            }
        }
        $resourceShare = ResourceShare::where('resource_type', 'document')
            ->where('resource_id', $document->id)
            ->where('user_id', $user->id)
            ->first();
        if ($resourceShare) {
            $rp = $resourceShare->permissions ?? [];
            return !empty($rp['delete']);
        }
        return false;
    }

    /**
     * Determine if the user can restore the document.
     */
    public function restore(User $user, Document $document): bool
    {
        return $this->delete($user, $document);
    }
}