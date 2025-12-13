<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WorkspaceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the user's workspaces
     */
    public function index()
    {
        $user = Auth::user();
        
        $workspaces = $user->workspaces
            ->load('documents', 'events');

        $sharedWorkspaces = $user->sharedWorkspaces
            ->load('documents', 'events');

        return Inertia::render('Workspaces/Index', [
            'workspaces' => $workspaces,
            'sharedWorkspaces' => $sharedWorkspaces,
        ]);
    }

    /**
     * Show the form for creating a new workspace
     */
    public function create()
    {
        return Inertia::render('Workspaces/Create');
    }

    /**
     * Store a newly created workspace
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_shared' => 'boolean',
        ]);

        $workspace = Auth::user()->workspaces()->create($validated);

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Workspace created successfully');
    }

    /**
     * Display the specified workspace
     */
    public function show(Workspace $workspace)
    {
        $this->authorize('view', $workspace);

        $workspace->load('documents', 'events', 'users');

        return Inertia::render('Workspaces/Show', [
            'workspace' => $workspace,
        ]);
    }

    /**
     * Show the form for editing the workspace
     */
    public function edit(Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        $workspace->load('documents', 'events', 'users');

        return Inertia::render('Workspaces/Edit', [
            'workspace' => $workspace,
        ]);
    }

    /**
     * Update the specified workspace
     */
    public function update(Request $request, Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_shared' => 'boolean',
        ]);

        $workspace->update($validated);

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Workspace updated successfully');
    }

    /**
     * Delete the specified workspace
     */
    public function destroy(Workspace $workspace)
    {
        $this->authorize('delete', $workspace);

        $workspace->delete();

        return redirect()->route('workspaces.index')
            ->with('success', 'Workspace deleted successfully');
    }

    /**
     * Add user to workspace (share)
     */
    public function addUser(Request $request, Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:editor,viewer',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user->id === $workspace->user_id) {
            return back()->withErrors('Cannot share with workspace owner');
        }

        $workspace->users()->syncWithoutDetaching([
            $user->id => ['role' => $validated['role']],
        ]);

        return back()->with('success', 'User added to workspace');
    }

    /**
     * Remove user from workspace
     */
    public function removeUser(Workspace $workspace, $userId)
    {
        $this->authorize('update', $workspace);

        $workspace->users()->detach($userId);

        return back()->with('success', 'User removed from workspace');
    }

    /**
     * Update user role in workspace
     */
    public function updateUserRole(Request $request, Workspace $workspace, $userId)
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'role' => 'required|in:editor,viewer',
        ]);

        $workspace->users()->updateExistingPivot($userId, $validated);

        return back()->with('success', 'User role updated');
    }
}
