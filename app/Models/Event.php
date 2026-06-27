<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'title',
        'description',
        'location',
        'starts_at',
        'ends_at',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * One-to-Many (inverse): the user who created the event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * One-to-Many (inverse): the blog post this event is attached to (optional).
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Many-to-Many: users who added this event to their personal calendar.
     */
    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user')->withTimestamps();
    }

    /**
     * Has the given user added this event to their calendar?
     */
    public function isSubscribedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->subscribers()->whereKey($user->id)->exists();
    }
}
