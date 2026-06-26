<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

/**
 * Authorization rules for posts. Used together with the auth middleware to
 * make sure only the author (or an admin) can modify or delete a post.
 */
class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->is_admin;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->is_admin;
    }
}
