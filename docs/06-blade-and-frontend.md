# 6. Blade Templating & Frontend

[← Previous: Auth & Security](05-auth-middleware-security.md) · [Back to index](README.md) · [Next: API →](07-api.md)

This chapter covers the **Blade Templating & UI Integration** rubric item (10 points). All views live in
`resources/views/`.

## 6.1 What is Blade?

**Blade** is Laravel's templating engine. It lets you write HTML with small embedded PHP-like
directives (`@if`, `@foreach`, `@extends`, …) and output data with `{{ }}`. Blade templates are
compiled to plain PHP and cached, so they're fast. Crucially, **`{{ $value }}` automatically escapes
HTML**, which protects against XSS attacks.

## 6.2 Layout inheritance — `@extends`, `@section`, `@yield`

Instead of repeating the `<html>`, navbar and footer on every page, we have **one master layout** and
every page plugs its content into it.

### The master layout

```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'KIU Blogger') — KIU Blogger</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- ...fonts, brand config... --}}
</head>
<body>
    @include('partials.navbar')          {{-- shared top navigation --}}

    <main>
        @include('partials.flash')       {{-- success/error messages --}}
        @yield('content')                {{-- ← each page injects its body here --}}
    </main>

    @include('partials.footer')
</body>
</html>
```

- **`@yield('content')`** is a placeholder. Child pages fill it.
- **`@yield('title', 'KIU Blogger')`** is a placeholder *with a default value*.

### A page that uses the layout

```blade
{{-- resources/views/posts/show.blade.php --}}
@extends('layouts.app')               {{-- "I am based on the master layout" --}}

@section('title', $post->title)       {{-- fill the title placeholder --}}

@section('content')                   {{-- fill the content placeholder --}}
    <article> ... the post ... </article>
@endsection
```

`@extends` says which layout to use; `@section` provides the content for each `@yield`. This is the
**template inheritance** the rubric asks about, demonstrated with `@extends`, `@section`, `@yield`.

## 6.3 Partials — `@include`

A **partial** is a reusable chunk of a template. We split the navbar, footer and flash messages into
`resources/views/partials/` and pull them in with `@include`:

```blade
@include('partials.navbar')
@include('partials.flash')
@include('partials.footer')
```

The create and edit post pages share the *same* form fields via a partial, so we don't duplicate the
form twice:

```blade
{{-- resources/views/posts/create.blade.php --}}
@include('posts._form', ['categories' => $categories, 'tags' => $tags])
```

The `['categories' => ...]` part passes data into the included partial. The same `_form.blade.php` is
reused by `edit.blade.php`, which additionally passes the existing `$post`. The partial checks
`@php($editing = isset($post))` to decide whether it's a create or edit form.

## 6.4 Components — `<x-...>`

A **Blade component** is a reusable, self-contained UI element with its own props — more powerful than a
plain include. We have two:

### `<x-post-card>` — the post preview card

```blade
{{-- resources/views/components/post-card.blade.php --}}
@props(['post'])      {{-- declares the data this component accepts --}}

<article>
    {{-- cover image or a placeholder, title, excerpt, author, date --}}
</article>
```

Used on the home page, the blog index and author pages — anywhere we show a grid of posts:

```blade
@foreach ($posts as $post)
    <x-post-card :post="$post" />
@endforeach
```

`:post="$post"` passes the `$post` object into the component's `$post` prop. **Write the card once, use
it everywhere** — that's the DRY (Don't Repeat Yourself) principle.

### `<x-validation-errors>` — the error summary box

```blade
{{-- resources/views/components/validation-errors.blade.php --}}
@if ($errors->any())
    <div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

Dropped into every form with a single tag: `<x-validation-errors />`. This is how validation failures
are *displayed* to the user (the rubric explicitly asks for "proper display of … error messages").

## 6.5 Control structures & directives in action

| Directive | What it does | Example in our code |
|-----------|--------------|---------------------|
| `@if / @else / @endif` | conditional | show Edit button only `@if` allowed |
| `@foreach` | loop | render each post card / comment / tag |
| `@forelse / @empty` | loop **with** an empty fallback | post list shows "No posts found" when empty |
| `@auth / @guest` | logged-in vs logged-out blocks | navbar shows "Write/Logout" vs "Login/Sign up" |
| `@can` | authorization check | `@can('update', $post)` hides Edit/Delete |
| `@csrf` | inserts the CSRF token | every form |
| `@method('PUT')` | spoofs PUT/DELETE verbs | edit & delete forms |
| `@error` | show a field's error | under each input |

### `@forelse` example (loop with empty state)

```blade
@forelse ($post->comments as $comment)
    <div>{{ $comment->body }} — {{ $comment->user->name }}</div>
@empty
    <p>No comments yet. Be the first to comment!</p>
@endforelse
```

### `@auth` / `@guest` example (navbar)

```blade
@auth
    <a href="{{ route('posts.create') }}">Write</a>
    {{-- ...profile menu, logout... --}}
@else
    <a href="{{ route('login') }}">Log in</a>
    <a href="{{ route('register') }}">Get started</a>
@endauth
```

### `@method` — why it's needed

HTML forms can only send GET and POST. To send PUT (update) or DELETE, Laravel uses **method spoofing**:

```blade
<form method="POST" action="{{ route('posts.destroy', $post) }}">
    @csrf
    @method('DELETE')      {{-- hidden _method=DELETE field --}}
    <button>Delete</button>
</form>
```

`@method('DELETE')` outputs `<input type="hidden" name="_method" value="DELETE">`, and Laravel routes
it to the `destroy()` method.

## 6.6 Named routes & `route()`

Notice we never hard-code URLs like `/posts/5`. We use **named routes**: `route('posts.show', $post)`.
If a URL ever changes, we change it in one place (`routes/web.php`) and every link updates. Named routes
come "for free" from `Route::resource`.

## 6.7 Pagination

`$posts->links()` in the index view renders Tailwind-styled pagination links automatically, because the
controller used `->paginate(6)`. `->withQueryString()` keeps the `?search=` / `?category=` filters
attached as you page through results.

## 6.8 The look & feel

Styling uses **Tailwind CSS** (utility classes like `rounded-2xl`, `text-slate-800`) loaded via CDN in
the layout, with a small custom brand palette (KIU blue `#0D47A1`). The UI includes a hero landing page,
a card-based blog grid, author profiles, and clean forms — giving the "good visuals / professional
standards" the brief asked for, without needing a separate frontend build step.

---

[← Previous: Auth & Security](05-auth-middleware-security.md) · [Back to index](README.md) · [Next: API →](07-api.md)
