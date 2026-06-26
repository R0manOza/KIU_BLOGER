# 9. Exam Prep — Q&A + Glossary

[← Previous: Deployment](08-deployment.md) · [Back to index](README.md)

This is your rapid-revision chapter. Practice saying these answers out loud. Each one points to the
chapter/file where the proof lives.

## 9.1 A suggested live-demo script (5 minutes)

Walk the professor through the app in this order — it naturally hits every rubric point:

1. **Home page** → "This is the landing page rendered by `HomeController` and a Blade layout."
2. **Register a new account** → "Session-based auth, validated input, password hashed, a 1:1 profile row created." *(Auth)*
3. **Write a post** with a cover image, a category and a few tags → "This is the Create in CRUD: validation, file upload, and a many-to-many tag relationship." *(CRUD + uploads + relationships)*
4. **View the post** → "Read — route-model binding by slug, eager-loaded author/category/tags/comments." *(MVC + relationships)*
5. **Comment on it** → "One-to-many: a comment belongs to a user and a post." *(relationships)*
6. **Edit then delete the post** → "Update and Delete, protected by an authorization Policy — only the owner can do this." *(CRUD + authorization)*
7. **Try to open `/categories` as a normal user** → 403. "Custom admin middleware." Then log in as admin and manage categories. *(middleware)*
8. **Open `/api/posts`** → "A JSON API via a resource controller and API Resource." *(API)*
9. Mention it's **deployed on Render with PostgreSQL via Docker**.

## 9.2 Likely questions & strong answers

### Architecture / MVC

**Q: Explain the MVC architecture in your project.**
> Model–View–Controller separates concerns. Models (`app/Models`) represent data and relationships and
> talk to the database via Eloquent. Views (`resources/views`) are Blade templates that only display
> data. Controllers (`app/Http/Controllers`) receive the request, use models to get/save data, and
> return a view or redirect. For example, `PostController@show` fetches a `Post` and passes it to
> `posts/show.blade.php`. See [Chapter 2](02-architecture.md).

**Q: What happens when I visit `/posts/some-slug`?**
> The request enters through `public/index.php`, the framework boots via `bootstrap/app.php`,
> `routes/web.php` matches it to `PostController@show`, route-model binding loads the `Post` by its slug,
> the controller eager-loads relations and returns the `posts.show` view, which renders HTML.

**Q: What is a route? What's a named route?**
> A route maps a URL + HTTP verb to a controller method (`routes/web.php`). A named route gives it a
> label like `posts.show` so we generate URLs with `route('posts.show', $post)` instead of hard-coding
> paths.

### Database / Eloquent

**Q: What is a migration and why use it?**
> A migration is a PHP class describing a table's structure. It's version control for the schema —
> `php artisan migrate` builds the database from code, so the schema is reproducible and shareable. See
> `database/migrations/`.

**Q: What is Eloquent / an ORM?**
> Eloquent is Laravel's ORM. It maps database tables to PHP model classes so we work with objects
> (`$post->user->name`) instead of writing SQL. Each model = one table.

**Q: Show me your three relationship types.** *(They WILL ask this.)*
> - **One-to-One:** `User hasOne Profile` (`User::profile()`), enforced by a unique FK.
> - **One-to-Many:** `User hasMany Post`, `Category hasMany Post`, `Post hasMany Comment`.
> - **Many-to-Many:** `Post belongsToMany Tag` through the `post_tag` pivot table.
>
> See [Chapter 3](03-database-and-eloquent.md#33-the-three-relationship-types-the-heart-of-the-rubric).

**Q: What's a pivot table?**
> The join table for a many-to-many relationship. `post_tag` holds `post_id` + `tag_id` pairs, linking
> posts and tags. Named by convention: the two models, singular, alphabetical, underscore-separated.

**Q: What is `$fillable` for?**
> It whitelists columns that can be mass-assigned via `create()`/`update()`, protecting against
> mass-assignment attacks (e.g. a user trying to set `is_admin` through a form).

**Q: What's the difference between `with()` and `load()`? / What is the N+1 problem?**
> Both eager-load relationships. `with()` is used when building a query; `load()` loads relations onto a
> model you already have. Eager loading prevents the **N+1 problem**: without it, looping over 10 posts
> and accessing `$post->user` would run 1 + 10 queries; with eager loading it's ~2 queries.

### CRUD / Validation

**Q: Where is your CRUD?**
> `PostController` implements all four: `create`+`store` (Create), `index`+`show` (Read), `edit`+`update`
> (Update), `destroy` (Delete). It's a resource controller. See [Chapter 4](04-crud-validation-uploads.md).

**Q: How does validation work?**
> `$request->validate([...rules...])`. If it passes, we get clean data; if it fails, Laravel redirects
> back with errors and the old input, and the Blade view shows them via `<x-validation-errors>` and
> `@error`. Example rules: `required`, `email`, `unique:users,email`, `image|mimes:...|max:4096`,
> `exists:categories,id`.

**Q: Why redirect after storing instead of returning a view?**
> The **Post/Redirect/Get** pattern — redirecting after a POST prevents the form being re-submitted if
> the user refreshes the page.

### File uploads

**Q: How do file uploads work?**
> The form has `enctype="multipart/form-data"`. We validate the file as an image, then
> `$request->file('cover_image')->store('covers', 'public')` saves it to the `public` disk and returns a
> path we store in the DB. `php artisan storage:link` exposes it to the web; `asset('storage/'.$path)`
> builds its URL. See [Chapter 4 §4.6](04-crud-validation-uploads.md#46-file-uploads--cover-images--avatars).

### Auth / Middleware / Security

**Q: How does authentication work? Did you use a package?**
> Simple session-based auth I wrote in `AuthController` using Laravel's `Auth` facade — no OAuth/packages.
> Register hashes the password with bcrypt and logs the user in; login uses `Auth::attempt()`; logout
> invalidates the session. See [Chapter 5](05-auth-middleware-security.md).

**Q: Authentication vs authorization?**
> Authentication = who you are (login). Authorization = what you're allowed to do (e.g. only the author
> can edit a post — enforced by `PostPolicy`).

**Q: What is middleware? Give an example you wrote.**
> Middleware filters requests before they reach the controller. The built-in `auth` middleware blocks
> guests from writing posts. I also wrote `EnsureUserIsAdmin` which returns 403 unless the user's
> `is_admin` is true — it protects the category-management routes.

**Q: What is CSRF and how do you protect against it?**
> Cross-Site Request Forgery — a malicious site submitting forms using your session. Laravel issues a
> per-session token; every form includes it via `@csrf`, and the middleware rejects requests without a
> valid token (419 error).

**Q: How are passwords stored?**
> As bcrypt hashes (`Hash::make()` + the model's `hashed` cast). Never in plaintext.

### Blade

**Q: What is Blade? Show template inheritance.**
> Laravel's templating engine. We have one master layout (`layouts/app.blade.php`) with `@yield`
> placeholders; pages `@extends` it and fill `@section`s. Reusable bits are pulled in with `@include`
> (partials) or used as `<x-...>` components. See [Chapter 6](06-blade-and-frontend.md).

**Q: Difference between a partial (`@include`) and a component (`<x-...>`)?**
> A partial is a simple template fragment that shares the parent's variables. A component is a
> self-contained, reusable element with its own declared props (`@props`) — more isolated and reusable,
> like `<x-post-card :post="$post" />`.

**Q: Why `{{ }}` and not `{!! !!}`?**
> `{{ }}` escapes HTML output (prevents XSS). `{!! !!}` outputs raw HTML and should only be used for
> trusted content. We use `{{ }}` everywhere user content is shown.

### API

**Q: What's the difference between your web routes and API routes?**
> Web routes return HTML, have sessions/cookies/CSRF. API routes (`routes/api.php`, prefixed `/api`) are
> stateless and return JSON. The API uses a separate `Api\PostController` and a `PostResource` to shape
> the JSON. See [Chapter 7](07-api.md).

**Q: What is an API Resource?**
> A class that transforms a model into a controlled JSON structure, so we expose exactly the fields we
> want (no passwords/internal columns) in a stable format.

## 9.3 If something breaks during the demo

- **Site is slow to load first time** → it's the Render free tier waking from sleep (~30–60s). Expected.
- **An uploaded image is missing** → ephemeral filesystem reset it after a redeploy; re-upload it.
- **Can't reach a page after login** → check you're logged in; protected routes redirect guests to `/login`.
- Have the **local version** ready as a backup: `php artisan serve` → `http://localhost:8000`.

## 9.4 Glossary (one-liners)

| Term | Meaning |
|------|---------|
| **MVC** | Model–View–Controller; separates data, presentation and request handling. |
| **Eloquent** | Laravel's ORM — database rows as PHP objects. |
| **ORM** | Object-Relational Mapper. |
| **Migration** | Code that defines/changes the database schema. |
| **Seeder** | Code that inserts demo/initial data. |
| **Relationship** | A link between models: one-to-one, one-to-many, many-to-many. |
| **Pivot table** | The join table for a many-to-many relationship (`post_tag`). |
| **CRUD** | Create, Read, Update, Delete. |
| **Resource controller** | A controller with the 7 standard CRUD methods. |
| **Route-model binding** | Auto-fetching a model from a URL parameter. |
| **Blade** | Laravel's templating engine. |
| **Directive** | A Blade command like `@if`, `@foreach`, `@extends`. |
| **Component** | A reusable Blade UI element (`<x-...>`). |
| **Middleware** | Code that filters a request before the controller. |
| **Policy** | A class holding per-model authorization rules. |
| **Authentication** | Verifying identity (login). |
| **Authorization** | Verifying permission (can you do X?). |
| **CSRF** | Cross-Site Request Forgery; blocked by per-session tokens. |
| **XSS** | Cross-Site Scripting; blocked by Blade's `{{ }}` escaping. |
| **Validation** | Checking user input against rules before use. |
| **Facade** | A static-style interface to a Laravel service (`Auth`, `Hash`, `Storage`). |
| **Eager loading** | Loading relationships up front to avoid the N+1 query problem. |
| **Query scope** | A reusable, named query constraint on a model (`->published()`). |
| **API** | An interface returning data (JSON) for programs to consume. |
| **API Resource** | A transformer that shapes a model into JSON. |
| **Migration vs Seeder** | Migration = structure (tables); Seeder = content (rows). |

---

Good luck — you've got this. 🎓

[← Previous: Deployment](08-deployment.md) · [Back to index](README.md)
