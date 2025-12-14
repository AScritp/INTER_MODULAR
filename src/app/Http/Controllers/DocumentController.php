<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display documents in a workspace
     */
    public function index(Workspace $workspace)
    {
        $this->authorize('view', $workspace);

        $documents = $workspace->documents()
            ->orderBy('order')
            ->get();

        return Inertia::render('Documents/Index', [
            'workspace' => $workspace,
            'documents' => $documents,
        ]);
    }

    /**
     * Get documents for a workspace in JSON format (for dashboard)
     */
    public function getDocumentsJson(Workspace $workspace)
    {
        try {
            $this->authorize('view', $workspace);

            $documents = $workspace->documents()
                ->orderBy('order')
                ->get();

            return response()->json($documents);
        } catch (\Exception $e) {
            \Log::error('Error loading documents: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar documentos'], 500);
        }
    }

    /**
     * Store a newly created document in JSON format (for dashboard)
     */
    public function storeJson(Request $request, Workspace $workspace)
    {
        try {
            $this->authorize('create', [Document::class, $workspace]);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'nullable|string',
            ]);

            $validated['user_id'] = Auth::id();
            $validated['order'] = $workspace->documents()->max('order') + 1;

            $document = $workspace->documents()->create($validated);

            return response()->json($document, 201);
        } catch (\Exception $e) {
            \Log::error('Error creating document: ' . $e->getMessage());
            return response()->json(['error' => 'Error al crear documento'], 500);
        }
    }

    /**
     * Delete a document in JSON format (for dashboard)
     */
    public function destroyJson(Workspace $workspace, Document $document)
    {
        try {
            $this->authorize('delete', $document);
            $document->delete();

            return response()->json(['message' => 'Documento eliminado'], 200);
        } catch (\Exception $e) {
            \Log::error('Error deleting document: ' . $e->getMessage());
            return response()->json(['error' => 'Error al eliminar documento'], 500);
        }
    }


    /**
     * Show the form for creating a new document
     */
    public function create(Workspace $workspace)
    {
        $this->authorize('create', [Document::class, $workspace]);

        return Inertia::render('Documents/Create', [
            'workspace' => $workspace,
        ]);
    }

    /**
     * Store a newly created document
     */
    public function store(Request $request, Workspace $workspace)
    {
        $this->authorize('create', [Document::class, $workspace]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['order'] = $workspace->documents()->max('order') + 1;

        $document = $workspace->documents()->create($validated);

        return redirect()->route('documents.edit', $document)
            ->with('success', 'Document created successfully');
    }

    /**
     * Show the document editor
     */
    public function edit(Document $document)
    {
        $this->authorize('update', $document);

        $document->load('workspace');

        return Inertia::render('Documents/Editor', [
            'document' => $document,
            'workspace' => $document->workspace,
        ]);
    }

    /**
     * Auto-save document content (API)
     */
    public function autoSave(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ]);

        $document->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Document saved',
            'document' => $document,
        ]);
    }

    /**
     * Update document metadata
     */
    public function update(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $document->update($validated);

        return back()->with('success', 'Document updated');
    }

    /**
     * Soft delete a document
     */
    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        $document->delete();

        return back()->with('success', 'Document deleted');
    }

    /**
     * Restore a soft-deleted document
     */
    public function restore($id)
    {
        $document = Document::withTrashed()->findOrFail($id);

        $this->authorize('delete', $document);

        $document->restore();

        return back()->with('success', 'Document restored');
    }

    /**
     * Update document order (for drag-and-drop)
     */
    public function updateOrder(Request $request, Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'integer',
        ]);

        foreach ($validated['documents'] as $order => $documentId) {
            Document::findOrFail($documentId)->update(['order' => $order]);
        }

        return response()->json(['success' => true]);
    }
}
