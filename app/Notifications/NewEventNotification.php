<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Notifications\Notification;

/**
 * Sent to followers who opted into a user's events when that user creates a
 * new event. The event is also auto-added to their calendar.
 */
class NewEventNotification extends Notification
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
            'kind' => 'new_event',
            'icon' => 'event',
            'message' => $this->event->creator->name . ' created an event: "' . $this->event->title
                . '" — added to your calendar',
            'url' => route('events.show', $this->event),
            'actor' => $this->event->creator->name,
        ];
    }
}
