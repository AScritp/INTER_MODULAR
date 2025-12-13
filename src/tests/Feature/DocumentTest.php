<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Workspace;
use App\Models\User;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    private $user;
    private $workspace;
    private $document;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->document = Document::factory()->create([
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Test que el usuario puede crear un documento
     */
    public function test_user_can_create_document()
    {
        $response = $this->actingAs($this->user)
            ->post("/workspaces/{$this->workspace->id}/documents", [
                'title' => 'Nuevo Documento',
                'content' => '<p>Contenido del documento</p>',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'title' => 'Nuevo Documento',
            'workspace_id' => $this->workspace->id,
        ]);
    }

    /**
     * Test que el autoguardado funciona
     */
    public function test_document_autosave_works()
    {
        $response = $this->actingAs($this->user)
            ->patchJson("/api/documents/{$this->document->id}/auto-save", [
                'title' => 'Título Actualizado',
                'content' => '<p>Contenido actualizado automáticamente</p>',
            ]);

        $response->assertStatus(200);
        $this->document->refresh();
        $this->assertEquals('Título Actualizado', $this->document->title);
    }

    /**
     * Test que el usuario puede eliminar su documento
     */
    public function test_user_can_delete_document()
    {
        $documentId = $this->document->id;

        $response = $this->actingAs($this->user)
            ->delete("/documents/{$this->document->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('documents', [
            'id' => $documentId,
            'deleted_at' => null,
        ]);
    }

    /**
     * Test que el usuario puede restaurar un documento eliminado
     */
    public function test_user_can_restore_document()
    {
        $this->document->delete();

        $response = $this->actingAs($this->user)
            ->patch("/documents/{$this->document->id}/restore");

        $this->document->refresh();
        $this->assertNull($this->document->deleted_at);
    }

    /**
     * Test que los documentos pueden reordenarse
     */
    public function test_documents_can_be_reordered()
    {
        $doc2 = Document::factory()->create([
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/workspaces/{$this->workspace->id}/documents/order", [
                'documents' => [$doc2->id, $this->document->id],
            ]);

        $response->assertStatus(200);
        $this->assertEquals(0, $doc2->fresh()->order);
        $this->assertEquals(1, $this->document->fresh()->order);
    }
}
