<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workspaces = Workspace::where('user_id', auth()->id())
            ->with(['documents', 'events'])
            ->orderBy('created_at', 'desc')
            ->get();

        $sharedWorkspaces = auth()->user()
            ->sharedWorkspaces()
            ->with(['documents', 'events', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Workspaces/Index', [
            'workspaces' => $workspaces,
            'sharedWorkspaces' => $sharedWorkspaces,
        ]);
    }

    /**
     * Obtener workspaces en formato JSON para el dashboard
     */
    public function getWorkspacesJson()
    {
        try {
            $workspaces = Workspace::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($workspaces);
        } catch (\Exception $e) {
            \Log::error('Error loading workspaces: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar workspaces'], 500);
        }
    }

    /**
     * Store a newly created resource in storage (JSON).
     */
    public function storeJson(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $workspace = Workspace::create([
                'user_id' => auth()->id(),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
                'is_shared' => false,
            ]);

            return response()->json($workspace, 201);
        } catch (\Exception $e) {
            \Log::error('Error creating workspace: ' . $e->getMessage());
            return response()->json(['error' => 'Error al crear workspace'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Workspaces/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_shared' => 'boolean',
        ]);

        $workspace = Workspace::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'is_shared' => $validated['is_shared'] ?? false,
        ]);

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Workspace creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Workspace $workspace)
    {
        $this->authorize('view', $workspace);

        $workspace->load(['documents', 'events', 'users', 'user']);

        return Inertia::render('Workspaces/Show', [
            'workspace' => $workspace,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        return Inertia::render('Workspaces/Edit', [
            'workspace' => $workspace,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_shared' => 'boolean',
        ]);

        $workspace->update($validated);

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Workspace actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage (JSON).
     */
    public function destroyJson(Workspace $workspace)
    {
        try {
            $this->authorize('delete', $workspace);
            $workspace->delete();

            return response()->json(['message' => 'Workspace eliminado'], 200);
        } catch (\Exception $e) {
            \Log::error('Error deleting workspace: ' . $e->getMessage());
            return response()->json(['error' => 'Error al eliminar workspace'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workspace $workspace)
    {
        $this->authorize('delete', $workspace);
        $workspace->delete();

        return redirect()->route('workspaces.index')
            ->with('success', 'Workspace eliminado exitosamente');
    }

    /**
     * Add a user to the workspace.
     */
    public function addUser(Request $request, Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:editor,viewer',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($workspace->users()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['email' => 'Este usuario ya tiene acceso al workspace']);
        }

        $workspace->users()->attach($user->id, ['role' => $validated['role']]);

        return back()->with('success', 'Usuario agregado exitosamente');
    }

    /**
     * Remove a user from the workspace.
     */
    public function removeUser(Workspace $workspace, User $user)
    {
        $this->authorize('update', $workspace);

        $workspace->users()->detach($user->id);

        return back()->with('success', 'Usuario removido exitosamente');
    }

    /**
     * Update user role in the workspace.
     */
    public function updateUserRole(Request $request, Workspace $workspace, User $user)
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'role' => 'required|in:editor,viewer',
        ]);

        $workspace->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return back()->with('success', 'Rol actualizado exitosamente');
    }
}