# 4. CRUD, Validation & File Uploads

[← Previous: Database & Eloquent](03-database-and-eloquent.md) · [Back to index](README.md) · [Next: Auth & Security →](05-auth-middleware-security.md)

This chapter covers the **CRUD Operations** rubric item (10 points) plus file uploads from the advanced
features item. CRUD = **C**reate, **R**ead, **U**pdate, **D**elete. Our `PostController`
(`app/Http/Controllers/PostController.php`) implements all four for blog posts.

## 4.1 CREATE — writing a new post

Two methods work together: `create()` shows the form, `store()` saves the submission.

### `create()` — show the form

```php
// app/Http/Controllers/PostController.php:67
public function create(): View
{
    $categories = Category::orderBy('name')->get();
    $tags = Tag::orderBy('name')->get();
    return view('posts.create', compact('categories', 'tags'));
}
```

It fetches categories and tags so the form can show a dropdown and tag checkboxes.

### `store()` — validate and save

```php
// app/Http/Controllers/PostController.php:75
public function store(Request $request): RedirectResponse
{
    $validated = $this->validatePost($request);          // 1. validate input

    $validated['user_id'] = $request->user()->id;        // 2. attach the logged-in author
    $validated['slug'] = $this->uniqueSlug($validated['title']);  // 3. build a unique URL slug
    $validated['published_at'] = now();

    if ($request->hasFile('cover_image')) {              // 4. handle the uploaded image
        $validated['cover_image'] = $request->file('cover_image')->store('covers', 'public');
    }

    $post = Post::create($validated);                    // 5. INSERT into the database
    $post->tags()->sync($request->input('tags', []));    // 6. attach the many-to-many tags

    return redirect()->route('posts.show', $post)        // 7. redirect to the new post
        ->with('success', 'Your post has been published!');
}
```

Walk the professor through these 7 steps — it touches validation, relationships, file uploads and the
**Post/Redirect/Get** pattern (we redirect after a successful POST so refreshing the page doesn't
re-submit the form).

## 4.2 READ — listing and showing posts

### `index()` — the listing, with search & filtering

```php
// app/Http/Controllers/PostController.php:31
public function index(Request $request): View
{
    $query = Post::with(['user', 'category', 'tags'])  // eager-load to avoid N+1 queries
        ->withCount('comments')                         // adds a comments_count number
        ->published()                                   // our custom scope
        ->latest();                                     // newest first

    if ($request->filled('category')) {                 // optional category filter
        $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
    }

    if ($request->filled('search')) {                   // optional search
        $term = $request->search;
        $query->where(fn ($q) => $q->where('title', 'like', "%{$term}%")
                                    ->orWhere('body', 'like', "%{$term}%"));
    }

    $posts = $query->paginate(6)->withQueryString();    // 6 per page, keep ?search=... in links
    $categories = Category::orderBy('name')->get();
    return view('posts.index', compact('posts', 'categories'));
}
```

Concepts here: **eager loading** (`with`), **aggregates** (`withCount`), **conditional query building**,
**`whereHas`** (filter by a related table), and **pagination**.

### `show()` — a single post

Covered in [Chapter 2](02-architecture.md#23-the-controller-asks-the-model): uses route-model binding
and eager-loads the author, category, tags and comments.

## 4.3 UPDATE — editing a post

`edit()` shows the pre-filled form, `update()` saves the changes.

```php
// app/Http/Controllers/PostController.php:104
public function update(Request $request, Post $post): RedirectResponse
{
    $this->authorize('update', $post);             // only the owner/admin may edit (see Ch.5)

    $validated = $this->validatePost($request);

    if ($request->hasFile('cover_image')) {
        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);  // delete the old image first
        }
        $validated['cover_image'] = $request->file('cover_image')->store('covers', 'public');
    }

    $post->update($validated);                     // UPDATE the database row
    $post->tags()->sync($request->input('tags', []));

    return redirect()->route('posts.show', $post)->with('success', 'Post updated successfully.');
}
```

Note the HTML form for editing uses `@method('PUT')` because browsers can only send GET/POST — Laravel
*spoofs* the PUT verb via a hidden field (explained in [Chapter 6](06-blade-and-frontend.md)).

## 4.4 DELETE — removing a post

```php
// app/Http/Controllers/PostController.php:125
public function destroy(Post $post): RedirectResponse
{
    $this->authorize('delete', $post);             // only the owner/admin may delete

    if ($post->cover_image) {
        Storage::disk('public')->delete($post->cover_image);  // clean up the file
    }

    $post->delete();
    return redirect()->route('posts.index')->with('success', 'Post deleted.');
}
```

We also delete comments automatically — that's handled by the database (`cascadeOnDelete()` on the
comments foreign key), so deleting a post removes its comments without extra code.

## 4.5 Validation — never trust user input

Both create and update share one validation method:

```php
// app/Http/Controllers/PostController.php:144
protected function validatePost(Request $request): array
{
    return $request->validate([
        'title'       => ['required', 'string', 'max:255'],
        'category_id' => ['nullable', 'exists:categories,id'],   // must be a real category
        'excerpt'     => ['nullable', 'string', 'max:500'],
        'body'        => ['required', 'string', 'min:20'],
        'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        'is_published'=> ['sometimes', 'boolean'],
        'tags'        => ['array'],
        'tags.*'      => ['exists:tags,id'],                     // every tag id must exist
    ]);
}
```

How validation behaves:

- If the data is **valid**, `$request->validate()` returns the clean data and execution continues.
- If it's **invalid**, Laravel automatically **redirects back** to the form, keeps the user's input
  (`old()` values), and fills the `$errors` bag — which the Blade view displays. The controller code
  after `validate()` never runs. This is why you'll see error messages appear above the form.

Rules worth explaining:
- `exists:categories,id` — the chosen category must actually exist (prevents tampering).
- `image|mimes:...|max:4096` — the upload must be an image of an allowed type, under 4 MB.
- `tags.*` — validates *each item* in the tags array.

## 4.6 File uploads — cover images & avatars

Uploading is three steps: an HTML form that can carry files, a validation rule, and storing the file.

### 1. The form must declare `enctype`

```blade
{{-- resources/views/posts/create.blade.php --}}
<form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
```

`enctype="multipart/form-data"` is **required** for file uploads — without it the file is not sent.

### 2. Store the file

```php
// app/Http/Controllers/PostController.php:84
$validated['cover_image'] = $request->file('cover_image')->store('covers', 'public');
```

- `->store('covers', 'public')` saves the file into `storage/app/public/covers/` with a random unique
  name and returns the relative path (e.g. `covers/abc123.jpg`), which we save in the database.
- The **`public` disk** is configured in `config/filesystems.php`. Files there are exposed to the web
  via a symbolic link created by `php artisan storage:link` (which links `public/storage` →
  `storage/app/public`).

### 3. Display the file

```php
// app/Models/Post.php
public function coverUrl(): ?string
{
    return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
}
```

`asset('storage/covers/abc123.jpg')` produces the full public URL the browser can load.

> **Avatars** work identically in `ProfileController::update()` — stored on the same `public` disk under
> `avatars/`, with the old avatar deleted when a new one is uploaded.

---

[← Previous: Database & Eloquent](03-database-and-eloquent.md) · [Back to index](README.md) · [Next: Auth & Security →](05-auth-middleware-security.md)
