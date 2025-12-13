<?php

namespace Tests\Feature;

use App\Models\Workspace;
use App\Models\User;
use App\Models\Document;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    private $user;
    private $workspace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Test que un usuario autenticado puede ver sus workspaces
     */
    public function test_user_can_view_workspaces()
    {
        $response = $this->actingAs($this->user)
            ->get('/workspaces');

        $response->assertStatus(200);
        $response->assertSee($this->workspace->name);
    }

    /**
     * Test que un usuario puede crear un workspace
     */
    public function test_user_can_create_workspace()
    {
        $response = $this->actingAs($this->user)
            ->post('/workspaces', [
                'name' => 'Nuevo Workspace',
                'description' => 'Descripción del nuevo workspace',
                'is_shared' => false,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('workspaces', [
            'name' => 'Nuevo Workspace',
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Test que el propietario puede editar su workspace
     */
    public function test_owner_can_update_workspace()
    {
        $response = $this->actingAs($this->user)
            ->patch("/workspaces/{$this->workspace->id}", [
                'name' => 'Workspace Actualizado',
                'description' => 'Nueva descripción',
            ]);

        $response->assertRedirect();
        $this->workspace->refresh();
        $this->assertEquals('Workspace Actualizado', $this->workspace->name);
    }

    /**
     * Test que el propietario puede eliminar su workspace
     */
    public function test_owner_can_delete_workspace()
    {
        $workspaceId = $this->workspace->id;

        $response = $this->actingAs($this->user)
            ->delete("/workspaces/{$this->workspace->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('workspaces', [
            'id' => $workspaceId,
        ]);
    }

    /**
     * Test que el propietario puede compartir su workspace
     */
    public function test_owner_can_share_workspace()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->post("/workspaces/{$this->workspace->id}/users", [
                'email' => $otherUser->email,
                'role' => 'editor',
            ]);

        $response->assertRedirect();
        $this->assertTrue(
            $this->workspace->users()->where('user_id', $otherUser->id)->exists()
        );
    }

    /**
     * Test que usuarios no autenticados no pueden ver workspaces
     */
    public function test_unauthenticated_user_cannot_view_workspaces()
    {
        $response = $this->get('/workspaces');

        $response->assertRedirect('/login');
    }
}
