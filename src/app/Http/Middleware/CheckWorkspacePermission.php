<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Workspace;
use Symfony\Component\HttpFoundation\Response;

class CheckWorkspacePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = 'view'): Response
    {
        $workspace = $request->route('workspace');

        if (!$workspace) {
            return $next($request);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Owner always has access
        if ($user->id === $workspace->user_id) {
            return $next($request);
        }

        // Check if user has access to shared workspace
        $role = $workspace->users()
            ->where('user_id', $user->id)
            ->pluck('role')
            ->first();

        if (!$role) {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        // Check permission based on role
        if ($permission === 'edit' && $role === 'viewer') {
            return response()->json(['message' => 'Permission Denied'], 403);
        }

        if ($permission === 'delete' && $role !== 'owner') {
            return response()->json(['message' => 'Permission Denied'], 403);
        }

        return $next($request);
    }
}
