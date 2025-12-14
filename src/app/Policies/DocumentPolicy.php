<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine if the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        $workspace = $document->workspace;

        // El propietario del workspace puede ver
        if ($workspace->user_id === $user->id) {
            return true;
        }

        // Los usuarios compartidos pueden ver
        return $workspace->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine if the user can create documents.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the document.
     */
    public function update(User $user, Document $document): bool
    {
        $workspace = $document->workspace;

        // El propietario del workspace puede actualizar
        if ($workspace->user_id === $user->id) {
            return true;
        }

        // Los usuarios con rol 'editor' pueden actualizar
        $pivot = $workspace->users()->where('user_id', $user->id)->first();
        return $pivot && $pivot->pivot->role === 'editor';
    }

    /**
     * Determine if the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        $workspace = $document->workspace;

        // El propietario del workspace puede eliminar
        if ($workspace->user_id === $user->id) {
            return true;
        }

        // Los usuarios con rol 'editor' pueden eliminar
        $pivot = $workspace->users()->where('user_id', $user->id)->first();
        return $pivot && $pivot->pivot->role === 'editor';
    }

    /**
     * Determine if the user can restore the document.
     */
    public function restore(User $user, Document $document): bool
    {
        return $this->delete($user, $document);
    }
}