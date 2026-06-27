<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Post;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class EventController extends Controller implements HasMiddleware
{
    /**
     * Everything here requires a logged-in user except viewing a single event.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth', except: ['show']),
        ];
    }

    /**
     * The calendar page (FullCalendar reads the JSON feed below).
     */
    public function index(): View
    {
        return view('events.calendar');
    }

    /**
     * JSON feed of the current user's calendar: events they created plus events
     * they subscribed to. Consumed by FullCalendar.
     */
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();

        $created = $user->createdEvents()->get();
        $subscribed = $user->subscribedEvents()->get();

        $events = $created->merge($subscribed)->unique('id')->map(function (Event $event) use ($user) {
            $mine = $event->user_id === $user->id;

            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->starts_at->toIso8601String(),
                'end' => $event->ends_at?->toIso8601String(),
                'url' => route('events.show', $event),
                'color' => $mine ? $event->color : '#1565C0',
                'extendedProps' => [
                    'mine' => $mine,
                    'location' => $event->location,
                ],
            ];
        })->values();

        return response()->json($events);
    }

    public function create(Request $request): View
    {
        // Optionally pre-link to a post passed as ?post={slug}.
        $post = $request->filled('post')
            ? Post::where('slug', $request->post)->first()
            : null;

        return view('events.create', compact('post'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateEvent($request);
        $data['user_id'] = $request->user()->id;

        // Allow linking to one of the user's own posts.
        if ($request->filled('post_id')) {
            $post = Post::find($request->post_id);
            $data['post_id'] = ($post && $post->user_id === $request->user()->id) ? $post->id : null;
        }

        $event = Event::create($data);

        EventService::handleCreated($event);

        return redirect()->route('events.show', $event)
            ->with('success', 'Event created and added to your calendar.');
    }

    public function show(Event $event): View
    {
        $event->load(['creator', 'post']);

        return view('events.show', compact('event'));
    }

    public function edit(Request $request, Event $event): View
    {
        $this->authorizeCreator($request, $event);

        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeCreator($request, $event);

        $event->update($this->validateEvent($request));

        EventService::handleUpdated($event);

        return redirect()->route('events.show', $event)
            ->with('success', 'Event updated — everyone who added it has been notified.');
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeCreator($request, $event);

        $event->delete();

        return redirect()->route('events.index')->with('success', 'Event deleted.');
    }

    /**
     * Add an event to the current user's personal calendar.
     */
    public function subscribe(Request $request, Event $event): RedirectResponse
    {
        $event->subscribers()->syncWithoutDetaching([$request->user()->id]);

        return back()->with('success', 'Added to your calendar.');
    }

    /**
     * Remove an event from the current user's calendar.
     */
    public function unsubscribe(Request $request, Event $event): RedirectResponse
    {
        $event->subscribers()->detach($request->user()->id);

        return back()->with('success', 'Removed from your calendar.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateEvent(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);
    }

    protected function authorizeCreator(Request $request, Event $event): void
    {
        abort_unless(
            $event->user_id === $request->user()->id || $request->user()->is_admin,
            403
        );
    }
}
