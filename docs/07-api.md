# 7. The JSON API

[← Previous: Blade & Frontend](06-blade-and-frontend.md) · [Back to index](README.md) · [Next: Deployment →](08-deployment.md)

This chapter covers the **API Basics / Resource Controllers / JSON responses** part of the advanced
features rubric item.

## 7.1 Web pages vs an API

So far every controller returned an HTML page (a Blade view). An **API (Application Programming
Interface)** returns **data** — here, **JSON** — instead of HTML. This lets other programs (a mobile
app, a JavaScript frontend, another server) consume our blog data.

Our API is **read-only**: it exposes published posts as JSON. It's perfect for demonstrating the concept
without the complexity of API authentication.

## 7.2 The API routes

API routes live in their own file and are automatically prefixed with `/api`:

```php
// routes/api.php
Route::apiResource('posts', PostController::class)
    ->only(['index', 'show'])
    ->names('api.posts');
```

This produces two endpoints:

| Method | URL | Returns |
|--------|-----|---------|
| GET | `/api/posts` | a paginated JSON list of published posts |
| GET | `/api/posts/{slug}` | one post as JSON |

Notes:
- **`apiResource`** is like `Route::resource` but without the `create`/`edit` form routes (an API has no
  HTML forms). We further limit it to `index` and `show` with `->only()`.
- **`->names('api.posts')`** namespaces the route names so they don't clash with the web `posts.*`
  routes. (We hit this exact bug during development — both files had a `posts.show`, so `route('posts.show')`
  became ambiguous. Renaming the API routes fixed it.)

## 7.3 The API controller

It's a separate controller under the `Api` namespace, so web and API concerns stay cleanly separated:

```php
// app/Http/Controllers/Api/PostController.php
class PostController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $posts = Post::with(['user', 'category', 'tags'])
            ->withCount('comments')
            ->published()
            ->latest()
            ->paginate(10);

        return PostResource::collection($posts);   // wrap the collection in our resource
    }

    public function show(Post $post): PostResource
    {
        $post->load(['user', 'category', 'tags'])->loadCount('comments');
        return new PostResource($post);
    }
}
```

It reuses the same Eloquent model and the `published()` scope as the web side — no duplicated data logic.

## 7.4 API Resources — shaping the JSON

We don't return the raw database row (that would leak internal columns and give us no control over the
shape). Instead an **API Resource** transforms a model into a tidy JSON structure:

```php
// app/Http/Resources/PostResource.php
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'cover_image' => $this->coverUrl(),
            'author' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'category' => $this->whenLoaded('category', fn () => $this->category?->name),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')),
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
            'published_at' => $this->published_at?->toDateTimeString(),
            'url' => route('posts.show', $this->slug),
        ];
    }
}
```

Why this is good practice:
- We choose **exactly** which fields are exposed (no password, no internal flags).
- **`whenLoaded('user')`** only includes the author block if it was eager-loaded — avoiding extra
  queries and keeping the JSON clean.
- The shape is stable and documented, independent of how the database happens to look.

## 7.5 What the JSON looks like

`GET /api/posts` returns something like:

```json
{
  "data": [
    {
      "id": 1,
      "title": "Welcome to KIU Blogger",
      "slug": "welcome-to-kiu-blogger",
      "excerpt": "A place for every student to share their voice.",
      "cover_image": null,
      "author": { "id": 3, "name": "Giorgi Kapanadze" },
      "category": "Campus Life",
      "tags": ["programming", "freshman", "research"],
      "comments_count": 3,
      "published_at": "2026-06-20 20:05:17",
      "url": "https://your-app.onrender.com/posts/welcome-to-kiu-blogger"
    }
  ],
  "links": { "first": "...", "last": "...", "next": "..." },
  "meta": { "current_page": 1, "total": 6, "per_page": 10 }
}
```

The `data`, `links` and `meta` wrapper (with pagination info) is added automatically by
`PostResource::collection()` on a paginated query.

## 7.6 How to demo it

In a browser or with `curl`:

```bash
curl https://your-app.onrender.com/api/posts
curl https://your-app.onrender.com/api/posts/welcome-to-kiu-blogger
```

Or just open `/api/posts` in the browser — you'll see the JSON. The
`bootstrap/app.php` config also makes errors return JSON for `api/*` routes, so the API always responds
with JSON, never an HTML error page.

---

[← Previous: Blade & Frontend](06-blade-and-frontend.md) · [Back to index](README.md) · [Next: Deployment →](08-deployment.md)
