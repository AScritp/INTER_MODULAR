<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceShare extends Model
{
    protected $fillable = [
        'workspace_id',
        'resource_type',
        'resource_id',
        'granter_id',
        'user_id',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function granter()
    {
        return $this->belongsTo(User::class, 'granter_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
