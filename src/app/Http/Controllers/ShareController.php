<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Document;
use App\Models\Event;
use App\Models\ResourceShare;
use App\Models\ShareInvitation;
use App\Notifications\ShareInvitationNotification;
use App\Notifications\ShareAcceptedNotification;
use App\Notifications\ShareRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShareController extends Controller
{
    public function shareWorkspace(Request $request, Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        $data = $request->validate([
            'email' => 'required|email',
            'permissions' => 'required|array',
            'inherit_existing_documents' => 'boolean',
            'inherit_existing_events' => 'boolean',
            'apply_to_future_only' => 'boolean',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Usuario no encontrado']);
        }

        $workspace->users()->syncWithoutDetaching([
            $user->id => [
                'role' => 'viewer',
                'permissions' => json_encode($data['permissions']),
                'inherit_existing_documents' => $data['inherit_existing_documents'] ?? false,
                'inherit_existing_events' => $data['inherit_existing_events'] ?? false,
                'apply_to_future_only' => $data['apply_to_future_only'] ?? false,
            ],
        ]);

        $invitation = ShareInvitation::create([
            'inviter_id' => Auth::id(),
            'invitee_id' => $user->id,
            'workspace_id' => $workspace->id,
            'permissions' => $data['permissions'],
            'status' => 'pending',
            'token' => Str::uuid()->toString(),
        ]);

        $user->notify(new ShareInvitationNotification($invitation));

        return back()->with('success', 'Workspace compartido y notificación enviada');
    }

    public function shareResource(Request $request, string $type, int $id)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'permissions' => 'required|array',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Usuario no encontrado']);
        }

        $resource = $type === 'document' ? Document::findOrFail($id) : Event::findOrFail($id);
        $workspace = $resource->workspace;

        $this->authorize('update', $workspace);

        $invitation = ShareInvitation::create([
            'inviter_id' => Auth::id(),
            'invitee_id' => $user->id,
            'workspace_id' => $workspace->id,
            'resource_type' => $type,
            'resource_id' => $id,
            'permissions' => $data['permissions'],
            'status' => 'pending',
            'token' => Str::uuid()->toString(),
        ]);

        $user->notify(new ShareInvitationNotification($invitation));

        return back()->with('success', ucfirst($type) . ' compartido y notificación enviada');
    }

    public function acceptInvitation(ShareInvitation $invitation)
    {
        $user = Auth::user();
        $inviteeMatches = ($invitation->invitee_id && $invitation->invitee_id === Auth::id())
            || ($invitation->invitee_email && $user && $invitation->invitee_email === $user->email);
        $hasNotification = $user
            ? $user->notifications()
                ->where('type', \App\Notifications\ShareInvitationNotification::class)
                ->where('data->invitation_id', $invitation->id)
                ->exists()
            : false;
        if ($invitation->status !== 'pending' || !$inviteeMatches) {
            if (!$hasNotification) {
                abort(403);
            }
            $inviteeMatches = true;
        }

        if (!$invitation->invitee_id && $user) {
            $invitation->invitee_id = $user->id;
        }

        $invitation->status = 'accepted';
        $invitation->save();

        $perm = is_string($invitation->permissions)
            ? json_decode($invitation->permissions, true)
            : (is_array($invitation->permissions) ? $invitation->permissions : []);

        if ($invitation->workspace_id && !$invitation->resource_type) {
            $invitation->workspace->users()->syncWithoutDetaching([
                Auth::id() => [
                    'role' => 'viewer',
                    'permissions' => json_encode($perm ?? []),
                ],
            ]);
        } else {
            ResourceShare::create([
                'workspace_id' => $invitation->workspace_id,
                'resource_type' => $invitation->resource_type,
                'resource_id' => $invitation->resource_id,
                'granter_id' => $invitation->inviter_id,
                'user_id' => Auth::id(),
                'permissions' => $perm ?? [],
            ]);

            // Also add workspace access so it appears in the user's shared workspaces
            if ($invitation->workspace_id) {
                $invitation->workspace->users()->syncWithoutDetaching([
                    Auth::id() => [
                        'role' => 'viewer',
                        'permissions' => json_encode($perm ?? []),
                    ],
                ]);
            }
        }

        $invitation->inviter->notify(new ShareAcceptedNotification($invitation));

        return back()->with('success', 'Invitación aceptada');
    }

    public function rejectInvitation(ShareInvitation $invitation)
    {
        $user = Auth::user();
        $inviteeMatches = ($invitation->invitee_id && $invitation->invitee_id === Auth::id())
            || ($invitation->invitee_email && $user && $invitation->invitee_email === $user->email);
        $hasNotification = $user
            ? $user->notifications()
                ->where('type', \App\Notifications\ShareInvitationNotification::class)
                ->where('data->invitation_id', $invitation->id)
                ->exists()
            : false;
        if ($invitation->status !== 'pending' || !$inviteeMatches) {
            if (!$hasNotification) {
                abort(403);
            }
            $inviteeMatches = true;
        }

        if (!$invitation->invitee_id && $user) {
            $invitation->invitee_id = $user->id;
        }

        $invitation->status = 'rejected';
        $invitation->save();

        $invitation->inviter->notify(new ShareRejectedNotification($invitation));

        return back()->with('success', 'Invitación rechazada');
    }
}
