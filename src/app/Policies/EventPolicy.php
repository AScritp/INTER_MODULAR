<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Models\Workspace;
use App\Models\ResourceShare;

class EventPolicy
{
    /**
     * Determine if the user can view the event
     */
    public function view(User $user, Event $event): bool
    {
        $workspace = $event->workspace;
        if ($user->id === $workspace->user_id) {
            return true;
        }
        if ($user->id === $event->user_id) {
            return true;
        }
        $shared = $workspace->users()->where('user_id', $user->id)->first();
        if ($shared) {
            $perm = $shared->pivot->permissions ?? null;
            if (is_array($perm)) {
                if (!empty($perm['read_existing_events'])) {
                    return true;
                }
                if (!empty($perm['read_own_events']) && $event->user_id === $user->id) {
                    return true;
                }
            }
            return true;
        }
        $resourceShare = ResourceShare::where('resource_type', 'event')
            ->where('resource_id', $event->id)
            ->where('user_id', $user->id)
            ->first();
        if ($resourceShare) {
            $rp = $resourceShare->permissions ?? [];
            return !empty($rp['read']);
        }
        return false;
    }

    /**
     * Determine if the user can create an event
     */
    public function create(User $user, Workspace $workspace): bool
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
            return !empty($perm['create_event']);
        }
        return ($shared->pivot->role ?? null) === 'editor';
    }

    /**
     * Determine if the user can update the event
     */
    public function update(User $user, Event $event): bool
    {
        $workspace = $event->workspace;
        if ($user->id === $workspace->user_id) {
            return true;
        }
        if ($user->id === $event->user_id) {
            return true;
        }
        $shared = $workspace->users()->where('user_id', $user->id)->first();
        if ($shared) {
            $perm = $shared->pivot->permissions ?? null;
            if (is_array($perm)) {
                if (!empty($perm['update_any_event'])) {
                    return true;
                }
                if (!empty($perm['update_own_event']) && $user->id === $event->user_id) {
                    return true;
                }
            }
            if (($shared->pivot->role ?? null) === 'editor') {
                return true;
            }
        }
        $resourceShare = ResourceShare::where('resource_type', 'event')
            ->where('resource_id', $event->id)
            ->where('user_id', $user->id)
            ->first();
        if ($resourceShare) {
            $rp = $resourceShare->permissions ?? [];
            return !empty($rp['update']);
        }
        return false;
    }

    /**
     * Determine if the user can delete the event
     */
    public function delete(User $user, Event $event): bool
    {
        $workspace = $event->workspace;
        if ($user->id === $workspace->user_id) {
            return true;
        }
        if ($user->id === $event->user_id) {
            return true;
        }
        $shared = $workspace->users()->where('user_id', $user->id)->first();
        if ($shared) {
            $perm = $shared->pivot->permissions ?? null;
            if (is_array($perm)) {
                if (!empty($perm['delete_any_event'])) {
                    return true;
                }
                if (!empty($perm['delete_own_event']) && $user->id === $event->user_id) {
                    return true;
                }
            }
            if (($shared->pivot->role ?? null) === 'editor') {
                return true;
            }
        }
        $resourceShare = ResourceShare::where('resource_type', 'event')
            ->where('resource_id', $event->id)
            ->where('user_id', $user->id)
            ->first();
        if ($resourceShare) {
            $rp = $resourceShare->permissions ?? [];
            return !empty($rp['delete']);
        }
        return false;
    }
}
