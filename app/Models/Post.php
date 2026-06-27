<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_image',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * One-to-Many (inverse): a post belongs to its author.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * One-to-Many (inverse): a post belongs to a category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * One-to-Many: a post has many comments.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    /**
     * One-to-Many: a post can have events linked to it.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Many-to-Many: a post can carry many tags.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * One-to-Many: a post collects many up/down votes.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Net score = (upvotes) - (downvotes). Uses the eager-loaded
     * `votes_sum_value` (from withSum) when available to avoid N+1 queries,
     * otherwise falls back to a direct SUM query.
     */
    public function score(): int
    {
        return (int) ($this->votes_sum_value ?? $this->votes()->sum('value'));
    }

    /**
     * The signed value (+1, -1, or 0) of the given user's vote on this post.
     */
    public function userVote(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        return (int) ($this->votes()->where('user_id', $user->id)->value('value') ?? 0);
    }

    /**
     * Query scope to fetch only published posts.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function coverUrl(): ?string
    {
        return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
    }

    /**
     * Rich-text body, sanitized for safe unescaped rendering. Legacy posts
     * stored as plain text are escaped and have their line breaks preserved.
     */
    public function bodyHtml(): string
    {
        $body = (string) $this->body;

        if (strip_tags($body) === $body) {
            return nl2br(e($body));
        }

        return HtmlSanitizer::clean($body);
    }

    /**
     * Plain-text version of the body for excerpts / meta (no HTML tags).
     */
    public function plainBody(): string
    {
        return trim(html_entity_decode(strip_tags((string) $this->body)));
    }
}
