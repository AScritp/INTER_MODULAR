// Fichero: AuthServiceProvider.php
<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\Workspace;
// Asegúrate de que todas las policies necesarias estén importadas
use App\Models\Event; // Agregado si quieres incluirlo
use App\Policies\DocumentPolicy;
use App\Policies\WorkspacePolicy;
use App\Policies\EventPolicy; // Agregado si quieres incluirlo
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Workspace::class => WorkspacePolicy::class,
        Document::class => DocumentPolicy::class,
        Event::class => EventPolicy::class, // Incluye esta línea para completar la migración
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies(); // Este método ya registra automáticamente todas las policies del array $policies
    }
}