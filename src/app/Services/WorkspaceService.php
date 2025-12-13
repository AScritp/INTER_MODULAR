<?php

namespace App\Services;

use App\Models\Workspace;
use App\Models\User;

class WorkspaceService
{
    /**
     * Verificar si un usuario tiene acceso a un workspace
     */
    public static function userHasAccess(User $user, Workspace $workspace, $requiredRole = null): bool
    {
        // Owner siempre tiene acceso
        if ($user->id === $workspace->user_id) {
            return true;
        }

        // Obtener rol del usuario en el workspace compartido
        $userRole = $workspace->users()
            ->where('user_id', $user->id)
            ->pluck('role')
            ->first();

        if (!$userRole) {
            return false;
        }

        // Si se especifica un rol requerido, verificarlo
        if ($requiredRole) {
            if ($requiredRole === 'editor') {
                return $userRole === 'editor';
            }
            if ($requiredRole === 'owner') {
                return false; // Solo el propietario puede ser "owner"
            }
        }

        return true;
    }

    /**
     * Obtener todos los workspaces accesibles para un usuario
     */
    public static function getAccessibleWorkspaces(User $user)
    {
        $owned = $user->workspaces();
        $shared = $user->sharedWorkspaces();

        return $owned->union($shared)->get();
    }

    /**
     * Invitar usuario a workspace
     */
    public static function inviteUser(Workspace $workspace, User $invitedUser, $role = 'editor')
    {
        return $workspace->users()->syncWithoutDetaching([
            $invitedUser->id => ['role' => $role],
        ]);
    }

    /**
     * Remover usuario de workspace
     */
    public static function removeUser(Workspace $workspace, User $user)
    {
        return $workspace->users()->detach($user->id);
    }
}
