<?php

namespace App\Notifications;

use App\Models\ShareInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShareInvitationNotification extends Notification
{
    use Queueable;

    public ShareInvitation $invitation;

    public function __construct(ShareInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'share_invitation',
            'invitation_id' => $this->invitation->id,
            'workspace_id' => $this->invitation->workspace_id,
            'resource_type' => $this->invitation->resource_type,
            'resource_id' => $this->invitation->resource_id,
            'permissions' => $this->invitation->permissions,
            'status' => $this->invitation->status,
            'inviter_id' => $this->invitation->inviter_id,
        ];
    }
}
