<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Post;
use App\Models\Tag;
use App\Notifications\NewPostNotification;
use App\Services\EventService;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PostController extends Controller implements HasMiddleware
{
    /**
     * Guests may browse (index/show); everything else requires authentication.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth', except: ['index', 'show']),
        ];
    }

    /**
     * Display a paginated, filterable listing of published posts.
     */
    public function index(Request $request): View
    {
        $query = Post::with(['user', 'category', 'tags'])
            ->withCount('comments')
            ->withSum('votes', 'value')
            ->published()
            ->latest();

        // Optional filtering by category slug and free-text search.
        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('body', 'like', "%{$term}%");
            });
        }

        $posts = $query->paginate(6)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('posts.index', compact('posts', 'categories'));
    }

    /**
     * Show a single post with its author, category, tags and comments.
     */
    public function show(Post $post): View
    {
        $post->load(['user.profile', 'category', 'tags', 'comments.user', 'events']);

        return view('posts.show', compact('post'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        return view('posts.create', compact('categories', 'tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePost($request);

        $validated['body'] = HtmlSanitizer::clean($validated['body']);
        $validated['user_id'] = $request->user()->id;
        $validated['slug'] = $this->uniqueSlug($validated['title']);
        $validated['published_at'] = now();

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $post = Post::create($validated);
        $post->tags()->sync($request->input('tags', []));

        $this->syncLinkedEvent($request, $post);
        $this->notifyFollowers($post);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Your post has been published!');
    }

    public function edit(Post $post): View
    {
        $this->authorize('update', $post);

        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        return view('posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $validated = $this->validatePost($request);

        if ($request->hasFile('cover_image')) {
            // Remove the previous image before storing the new one.
            if ($post->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $validated['body'] = HtmlSanitizer::clean($validated['body']);

        $post->update($validated);
        $post->tags()->sync($request->input('tags', []));

        $this->syncLinkedEvent($request, $post);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }

        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted.');
    }

    /**
     * Shared validation rules for creating and updating a post.
     *
     * @return array<string, mixed>
     */
    protected function validatePost(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_published' => ['sometimes', 'boolean'],
            'tags' => ['array'],
            'tags.*' => ['exists:tags,id'],
            // Optional linked event.
            'event_title' => ['nullable', 'string', 'max:255'],
            'event_starts_at' => ['nullable', 'required_with:event_title', 'date'],
            'event_ends_at' => ['nullable', 'date', 'after_or_equal:event_starts_at'],
            'event_location' => ['nullable', 'string', 'max:255'],
        ]);

        // The body must contain real text once HTML is stripped.
        if (HtmlSanitizer::isEmpty($validated['body'])) {
            throw ValidationException::withMessages([
                'body' => 'The post body is required.',
            ]);
        }

        return $validated;
    }

    /**
     * Create or update the single event optionally attached to a post.
     */
    protected function syncLinkedEvent(Request $request, Post $post): void
    {
        if (! $request->filled('event_title')) {
            return;
        }

        $payload = [
            'user_id' => $post->user_id,
            'title' => $request->input('event_title'),
            'location' => $request->input('event_location'),
            'starts_at' => $request->input('event_starts_at'),
            'ends_at' => $request->input('event_ends_at'),
            'description' => Str::limit($post->plainBody(), 200),
        ];

        $event = $post->events()->first();

        if ($event) {
            $event->update($payload);
            EventService::handleUpdated($event);
        } else {
            $event = $post->events()->create($payload);
            EventService::handleCreated($event);
        }
    }

    /**
     * Notify the author's followers about a newly published post.
     */
    protected function notifyFollowers(Post $post): void
    {
        if (! $post->is_published) {
            return;
        }

        $followers = $post->user->followers()->get();

        if ($followers->isNotEmpty()) {
            Notification::send($followers, new NewPostNotification($post));
        }
    }

    protected function uniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 1;

        while (Post::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $counter++;
        }

        return $slug;
    }
}
