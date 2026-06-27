<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Notifications\Notification;

/**
 * Sent to everyone who added an event to their calendar when the creator
 * edits it, so subscribers know their calendar entry changed.
 */
class EventUpdatedNotification extends Notification
{
    public function __construct(public Event $event)
    {
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'event_updated',
            'icon' => 'event',
            'message' => '"' . $this->event->title . '" was updated by ' . $this->event->creator->name,
            'url' => route('events.show', $this->event),
            'actor' => $this->event->creator->name,
        ];
    }
}
