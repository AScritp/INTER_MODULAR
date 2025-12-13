<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Relación: Workspace pertenece a un Usuario (propietario)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Workspace tiene muchos Documentos
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Relación: Workspace tiene muchos Eventos
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Relación: Workspace puede ser compartido con múltiples Usuarios
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Obtener todos los usuarios con acceso (propietario + compartidos)
     */
    public function allUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_user')
            ->withPivot('role')
            ->withTimestamps()
            ->union(
                $this->user()->getQuery()->select('users.*')
                    ->selectRaw("'owner' as role")
            );
    }
}
