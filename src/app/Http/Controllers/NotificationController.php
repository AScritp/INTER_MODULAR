<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = $user
            ? $user->notifications()->orderBy('created_at', 'desc')->get()
            : collect();

        return Inertia::render('Notifications/Inbox', [
            'notifications' => $notifications,
        ]);
    }

    public function count()
    {
        if (!Auth::check()) {
            return response()->json(['count' => 0]);
        }
        $count = Auth::user()
            ->notifications()
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markRead(string $id, Request $request)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }
        return back();
    }
}
