<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_shared',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
    ];

    /**
     * Get the user that owns the workspace.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the documents for the workspace.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get the events for the workspace.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * The users that have access to the workspace (shared users).
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'workspace_user')
            ->withPivot('role')
            ->withTimestamps();
    }
}