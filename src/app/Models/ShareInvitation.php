<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShareInvitation extends Model
{
    protected $fillable = [
        'inviter_id',
        'invitee_id',
        'invitee_email',
        'workspace_id',
        'resource_type',
        'resource_id',
        'permissions',
        'status',
        'token',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invitee()
    {
        return $this->belongsTo(User::class, 'invitee_id');
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
