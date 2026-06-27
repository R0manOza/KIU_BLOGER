<?php

namespace App\Services;

use App\Models\Event;
use App\Notifications\EventUpdatedNotification;
use App\Notifications\NewEventNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Shared event side-effects so both EventController and PostController behave
 * identically when an event is created or changed.
 */
class EventService
{
    /**
     * When a new event is created: auto-subscribe everyone who follows the
     * creator's events and notify them.
     */
    public static function handleCreated(Event $event): void
    {
        $followers = $event->creator
            ->followers()
            ->wherePivot('follow_events', true)
            ->get();

        if ($followers->isEmpty()) {
            return;
        }

        // Auto-add the event to each follower's calendar (idempotent).
        $event->subscribers()->syncWithoutDetaching($followers->pluck('id')->all());

        Notification::send($followers, new NewEventNotification($event));
    }

    /**
     * When an event is edited, tell everyone who has it on their calendar.
     * The calendar entry itself updates automatically (subscribers reference
     * the same event row) — this is just the heads-up.
     */
    public static function handleUpdated(Event $event): void
    {
        $subscribers = $event->subscribers()->get();

        if ($subscribers->isNotEmpty()) {
            Notification::send($subscribers, new EventUpdatedNotification($event));
        }
    }
}
