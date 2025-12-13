<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkspaceController extends Controller
{
    // Métodos JSON para el Dashboard
    public function getWorkspacesJson(Request $request)
    {
        $workspaces = $request->user()->workspaces()->latest()->get();
        return response()->json($workspaces);
    }

    public function storeJson(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        $workspace = $request->user()->workspaces()->create($validated);
        return response()->json($workspace, 201);
    }

    public function destroyJson(Request $request, Workspace $workspace)
    {
        if ($workspace->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $workspace->delete();
        return response()->json(['message' => 'Workspace deleted successfully']);
    }

    // Métodos originales con Inertia (mantén los que ya tienes)
    public function index(Request $request)
    {
        $workspaces = $request->user()->workspaces()->latest()->get();
        
        return Inertia::render('Workspaces/Index', [
            'workspaces' => $workspaces
        ]);
    }

    public function create()
    {
        return Inertia::render('Workspaces/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        $workspace = $request->user()->workspaces()->create($validated);

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Workspace creado exitosamente');
    }

    public function show(Request $request, Workspace $workspace)
    {
        if ($workspace->user_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        return Inertia::render('Workspaces/Show', [
            'workspace' => $workspace
        ]);
    }

    public function edit(Request $request, Workspace $workspace)
    {
        if ($workspace->user_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        return Inertia::render('Workspaces/Edit', [
            'workspace' => $workspace
        ]);
    }

    public function update(Request $request, Workspace $workspace)
    {
        if ($workspace->user_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        $workspace->update($validated);

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Workspace actualizado exitosamente');
    }

    public function destroy(Request $request, Workspace $workspace)
    {
        if ($workspace->user_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        $workspace->delete();

        return redirect()->route('workspaces.index')
            ->with('success', 'Workspace eliminado exitosamente');
    }

    public function addUser(Request $request, Workspace $workspace)
    {
        // Implementar lógica para añadir usuarios
    }

    public function removeUser(Request $request, Workspace $workspace, $userId)
    {
        // Implementar lógica para eliminar usuarios
    }

    public function updateUserRole(Request $request, Workspace $workspace, $userId)
    {
        // Implementar lógica para actualizar rol de usuario
    }
}