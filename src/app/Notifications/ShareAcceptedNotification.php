<?php

namespace App\Notifications;

use App\Models\ShareInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShareAcceptedNotification extends Notification
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
            'type' => 'share_accepted',
            'invitation_id' => $this->invitation->id,
            'invitee_id' => $this->invitation->invitee_id,
        ];
    }
}
