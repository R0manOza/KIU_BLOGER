<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Notifications\Notification;

/**
 * Sent to a user's followers when they publish a new post.
 * Database channel only (in-app), dispatched synchronously.
 */
class NewPostNotification extends Notification
{
    public function __construct(public Post $post)
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
            'kind' => 'new_post',
            'icon' => 'post',
            'message' => $this->post->user->name . ' published a new post: "' . $this->post->title . '"',
            'url' => route('posts.show', $this->post),
            'actor' => $this->post->user->name,
        ];
    }
}
