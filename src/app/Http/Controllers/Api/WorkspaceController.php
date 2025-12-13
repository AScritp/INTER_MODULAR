<?php

namespace App\Http\Controllers\Api;  // ← IMPORTANTE: Debe incluir "\Api"

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $workspaces = $request->user()->workspaces()->latest()->get();
        return response()->json($workspaces);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        $workspace = $request->user()->workspaces()->create($validated);

        return response()->json($workspace, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Workspace $workspace)
    {
        if ($workspace->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($workspace);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Workspace $workspace)
    {
        if ($workspace->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        $workspace->update($validated);

        return response()->json($workspace);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Workspace $workspace)
    {
        if ($workspace->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $workspace->delete();

        return response()->json(['message' => 'Workspace deleted successfully']);
    }
}