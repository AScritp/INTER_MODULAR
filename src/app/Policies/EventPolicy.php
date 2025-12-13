<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Models\Workspace;

class EventPolicy
{
    /**
     * Determine if the user can view the event
     */
    public function view(User $user, Event $event): bool
    {
        $workspace = $event->workspace;

        // Owner of workspace can view
        if ($user->id === $workspace->user_id) {
            return true;
        }

        // Creator can view
        if ($user->id === $event->user_id) {
            return true;
        }

        // Shared users with any role can view
        return $workspace->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine if the user can create an event
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
     * Determine if the user can update the event
     */
    public function update(User $user, Event $event): bool
    {
        $workspace = $event->workspace;

        // Owner can update
        if ($user->id === $workspace->user_id) {
            return true;
        }

        // Creator can update
        if ($user->id === $event->user_id) {
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
     * Determine if the user can delete the event
     */
    public function delete(User $user, Event $event): bool
    {
        $workspace = $event->workspace;

        // Only workspace owner can delete
        if ($user->id === $workspace->user_id) {
            return true;
        }

        // Only creators can delete their own
        return $user->id === $event->user_id;
    }
}
