<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EventController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display events in a workspace
     */
    public function index(Workspace $workspace)
    {
        $this->authorize('view', $workspace);

        $events = $workspace->events()->get();

        return Inertia::render('Events/Index', [
            'workspace' => $workspace,
            'events' => $events,
        ]);
    }

    /**
     * Show calendar view
     */
    public function calendar(Workspace $workspace)
    {
        $this->authorize('view', $workspace);

        $events = $workspace->events()->get();

        return Inertia::render('Events/Calendar', [
            'workspace' => $workspace,
            'events' => $events,
        ]);
    }

    /**
     * Show the form for creating a new event
     */
    public function create(Workspace $workspace)
    {
        $this->authorize('create', [Event::class, $workspace]);

        return Inertia::render('Events/Create', [
            'workspace' => $workspace,
        ]);
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request, Workspace $workspace)
    {
        $this->authorize('create', [Event::class, $workspace]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'start_date' => 'required|date_format:Y-m-d H:i',
            'end_date' => 'required|date_format:Y-m-d H:i|after:start_date',
        ]);

        $validated['user_id'] = Auth::id();

        $event = $workspace->events()->create($validated);

        return redirect()->route('events.calendar', $workspace)
            ->with('success', 'Event created successfully');
    }

    /**
     * Show the form for editing an event
     */
    public function edit(Event $event)
    {
        $this->authorize('update', $event);

        $event->load('workspace');

        return Inertia::render('Events/Edit', [
            'event' => $event,
            'workspace' => $event->workspace,
        ]);
    }

    /**
     * Update the specified event
     */
    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'start_date' => 'required|date_format:Y-m-d H:i',
            'end_date' => 'required|date_format:Y-m-d H:i|after:start_date',
        ]);

        $event->update($validated);

        return back()->with('success', 'Event updated successfully');
    }

    /**
     * Delete the specified event
     */
    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        $workspace = $event->workspace;
        $event->delete();

        return redirect()->route('events.calendar', $workspace)
            ->with('success', 'Event deleted successfully');
    }

    /**
     * Get events for calendar (API)
     */
    public function getCalendarEvents(Workspace $workspace)
    {
        $this->authorize('view', $workspace);

        $events = $workspace->events()
            ->select('id', 'title', 'start_date', 'end_date', 'description')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->start_date->toIso8601String(),
                    'end' => $event->end_date->toIso8601String(),
                    'extendedProps' => [
                        'description' => $event->description,
                    ],
                ];
            });

        return response()->json($events);
    }
}
