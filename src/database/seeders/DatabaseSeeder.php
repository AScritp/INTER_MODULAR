<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Event;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario de prueba
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Crear workspaces
        $workspace1 = Workspace::create([
            'user_id' => $user->id,
            'name' => 'Mi Proyecto Personal',
            'description' => 'Un workspace para mis proyectos personales',
            'is_shared' => false,
        ]);

        $workspace2 = Workspace::create([
            'user_id' => $user->id,
            'name' => 'Notas de Estudio',
            'description' => 'Apuntes de mis cursos',
            'is_shared' => true,
        ]);

        // Crear documentos
        Document::create([
            'workspace_id' => $workspace1->id,
            'user_id' => $user->id,
            'title' => 'Documento de Bienvenida',
            'content' => '<h2>¡Bienvenido a Notion-like!</h2><p>Este es tu primer documento. Puedes editarlo, arrastrarlo y compartirlo con otros usuarios.</p>',
            'order' => 1,
        ]);

        Document::create([
            'workspace_id' => $workspace2->id,
            'user_id' => $user->id,
            'title' => 'Apuntes PHP',
            'content' => '<h3>Conceptos Básicos</h3><ul><li>Variables y Tipos</li><li>Funciones</li><li>Clases y Objetos</li></ul>',
            'order' => 1,
        ]);

        // Crear eventos
        Event::create([
            'workspace_id' => $workspace1->id,
            'user_id' => $user->id,
            'title' => 'Entrega de Proyecto',
            'description' => 'Fecha límite para entregar el proyecto',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(7)->addHours(2),
        ]);

        Event::create([
            'workspace_id' => $workspace2->id,
            'user_id' => $user->id,
            'title' => 'Examen de Programación',
            'description' => 'Examen final del curso',
            'start_date' => now()->addDays(14),
            'end_date' => now()->addDays(14)->addHours(3),
        ]);

        // Crear usuario adicional para pruebas de compartición
        $user2 = User::factory()->create([
            'name' => 'Collaborator User',
            'email' => 'collaborator@example.com',
        ]);

        // Compartir workspace2 con user2
        $workspace2->users()->attach($user2->id, ['role' => 'editor']);
    }
}

