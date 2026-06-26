# 2. Architecture & Request Lifecycle (MVC)

[← Previous: Overview](01-overview.md) · [Back to index](README.md) · [Next: Database & Eloquent →](03-database-and-eloquent.md)

## 2.1 What is MVC?

**MVC = Model–View–Controller.** It is a design pattern that separates an application into three
responsibilities so the code stays organised and maintainable:

| Part | Responsibility | In our project |
|------|----------------|----------------|
| **Model** | Represents data and business logic; talks to the database. | `app/Models/*` (e.g. `Post`, `User`) |
| **View** | Presents data to the user as HTML. | `resources/views/*` (Blade templates) |
| **Controller** | Receives the request, coordinates Models and Views, returns a response. | `app/Http/Controllers/*` |

The golden rule: **controllers stay thin, models hold data logic, views only display.** A controller
should never build HTML, and a view should never run database queries directly.

## 2.2 The request lifecycle — following one real request

Let's trace exactly what happens when a visitor clicks a post and the browser requests
`GET /posts/welcome-to-kiu-blogger`.

```
Browser
  │  GET /posts/welcome-to-kiu-blogger
  ▼
public/index.php            ← single entry point (front controller)
  ▼
bootstrap/app.php           ← boots the framework, registers routing + middleware
  ▼
routes/web.php              ← matches the URL to a route
  ▼
Middleware                  ← request passes through filters (sessions, CSRF, etc.)
  ▼
PostController@show         ← the Controller method runs
  ▼
Post model (Eloquent)       ← Controller asks the Model for data → SQL → database
  ▼
resources/views/posts/show.blade.php  ← Controller passes data to the View
  ▼
HTML response → Browser
```

### Step 1 — The route

Every URL is declared in `routes/web.php`. Because posts use a *resource controller*, the whole set of
post routes is registered with one line:

```php
// routes/web.php:45
Route::resource('posts', PostController::class);
```

`Route::resource` generates the seven conventional CRUD routes automatically:

| Verb | URL | Controller method | Purpose |
|------|-----|-------------------|---------|
| GET | `/posts` | `index` | list posts |
| GET | `/posts/create` | `create` | show "new post" form |
| POST | `/posts` | `store` | save a new post |
| GET | `/posts/{post}` | `show` | show one post |
| GET | `/posts/{post}/edit` | `edit` | show "edit" form |
| PUT/PATCH | `/posts/{post}` | `update` | save edits |
| DELETE | `/posts/{post}` | `destroy` | delete a post |

> **Why a resource controller?** It is the idiomatic Laravel way to express CRUD, it guarantees correct
> route naming/ordering, and it documents intent. This is a strong point to mention for the *CRUD* and
> *MVC* rubric items.

### Step 2 — Route–Model binding

Notice the route is `/posts/{post}` but our method receives a fully-loaded `Post` object:

```php
// app/Http/Controllers/PostController.php:60
public function show(Post $post): View
{
    $post->load(['user.profile', 'category', 'tags', 'comments.user']);
    return view('posts.show', compact('post'));
}
```

This is **implicit route–model binding**. Laravel sees the type-hint `Post $post`, takes the
`{post}` value from the URL, and automatically fetches the matching row from the database. We use the
*slug* (a URL-friendly title) instead of the numeric id because the `Post` model declares:

```php
// app/Models/Post.php — getRouteKeyName()
public function getRouteKeyName(): string
{
    return 'slug';
}
```

So `welcome-to-kiu-blogger` is looked up against the `slug` column.

### Step 3 — The controller asks the model

`$post->load([...])` is **eager loading**. Instead of querying the database again and again for the
author, category, tags and comments while rendering the page (the "N+1 query problem"), it loads them
all up front in a few efficient queries. The `user.profile` and `comments.user` syntax loads
*nested* relationships (the author's profile, and each comment's author).

### Step 4 — The controller hands data to the view

`return view('posts.show', compact('post'));` renders
`resources/views/posts/show.blade.php`, passing the `$post` object into it. The view only *displays*
data — it never queries the database itself. (See [Chapter 6](06-blade-and-frontend.md).)

## 2.3 Where each MVC piece is enforced

- **Models never render HTML.** They define data, relationships and small helpers (e.g.
  `Post::coverUrl()` returns an image URL).
- **Controllers never build HTML.** They validate input, talk to models, and return a view or redirect.
- **Views never query the database.** They receive ready-made data from the controller.

This clean separation is precisely what the *"Proper separation and practical implementation of Model,
View, and Controller"* criterion is asking for.

## 2.4 Two route files: web vs API

```php
// bootstrap/app.php:9
->withRouting(
    web: __DIR__.'/../routes/web.php',   // returns HTML (Blade)
    api: __DIR__.'/../routes/api.php',   // returns JSON, prefixed with /api
    commands: __DIR__.'/../routes/console.php',
    health: '/up',                       // health-check endpoint used by Render
)
```

- **`routes/web.php`** → has session state, cookies and CSRF protection; returns HTML pages.
- **`routes/api.php`** → stateless; automatically prefixed with `/api`; returns JSON. (See [Chapter 7](07-api.md).)

This separation of "web" and "api" route groups with different middleware is itself a Laravel
architectural feature worth naming.

---

[← Previous: Overview](01-overview.md) · [Back to index](README.md) · [Next: Database & Eloquent →](03-database-and-eloquent.md)
