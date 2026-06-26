# KIU Blogger

A fully functional, CRUD-based blogging platform for **Kutaisi International University (KIU)** students,
built with **PHP & the Laravel framework**. Students can register, write and manage posts (with cover
images and tags), comment on each other's articles, and personalise their profiles. Administrators can
manage the blog's categories.

This is my final project for the Laravel course.

---

## Tech stack

- **PHP 8.2+ / Laravel 13** (MVC framework)
- **Blade** templating engine
- **Eloquent ORM** with SQLite (development) / PostgreSQL (production)
- **Tailwind CSS** for the UI

---

## How the project meets the grading rubric

### 1. MVC & Database Management
- **Models** (`app/Models`): `User`, `Profile`, `Category`, `Post`, `Tag`, `Comment`.
- **Migrations** (`database/migrations`): one migration per table plus a pivot table.
- **Relationships** demonstrated:
  - **One-to-One** — `User` ↔ `Profile`
  - **One-to-Many** — `User` → `Post`, `Category` → `Post`, `Post` → `Comment`, `User` → `Comment`
  - **Many-to-Many** — `Post` ↔ `Tag` (via the `post_tag` pivot table)

### 2. CRUD Operations
- Full Create / Read / Update / Delete for **Posts** (resource controller `PostController`).
- CRUD for **Categories** (admin) and create/delete for **Comments**.
- All write forms include **CSRF protection** (`@csrf`) and use proper HTTP verbs (`@method('PUT'/'DELETE')`).

### 3. Blade Templating & UI
- A master layout (`resources/views/layouts/app.blade.php`) using `@yield` / `@section` / `@extends`.
- Reusable **partials** (`@include`) for the navbar, footer and flash messages.
- Reusable **Blade components** (`<x-post-card>`, `<x-validation-errors>`).
- Control structures throughout: `@foreach`, `@forelse`, `@if`, `@auth`, `@guest`, `@can`.

### 4. Security, Validation & Advanced Features
- **Validation** on every form, with errors displayed in Blade.
- **Authentication** — simple, session-based register / login / logout (no OAuth), using Laravel's `Auth` facade.
- **Middleware** — Laravel's `auth` guard plus a custom `EnsureUserIsAdmin` middleware for the admin area, and a `PostPolicy` for owner-only editing.
- **File uploads** — post cover images and user avatars stored on the `public` disk.
- **JSON API** — a read-only resource controller (`Api\PostController`) exposing `/api/posts` and `/api/posts/{slug}` via an API Resource.

---

## Running it locally

Requirements: **PHP 8.2+** and **Composer**.

```bash
# 1. Install dependencies
composer install

# 2. Set up the environment
cp .env.example .env
php artisan key:generate

# 3. Create the SQLite database file
#    (Windows PowerShell: New-Item database/database.sqlite)
touch database/database.sqlite

# 4. Run migrations and seed demo data
php artisan migrate --seed

# 5. Link the storage directory (for uploaded images)
php artisan storage:link

# 6. Start the server
php artisan serve
```

Then open <http://localhost:8000>.

### Demo accounts (created by the seeder)

| Role  | Email               | Password   |
|-------|---------------------|------------|
| Admin | `admin@kiu.edu.ge`  | `password` |
| User  | `nino@kiu.edu.ge`   | `password` |
| User  | `giorgi@kiu.edu.ge` | `password` |

> The admin account can manage categories at `/categories`.

---

## API

| Method | Endpoint            | Description                   |
|--------|---------------------|-------------------------------|
| GET    | `/api/posts`        | Paginated list of published posts (JSON) |
| GET    | `/api/posts/{slug}` | A single post (JSON)          |

---

## Deploying to Render

This repo ships with a `Dockerfile` and a `render.yaml` Blueprint.

1. Push the repository to GitHub.
2. In the Render Dashboard: **New → Blueprint** and select this repo.
3. Render provisions a free PostgreSQL database and the web service automatically.
4. Set the `APP_KEY` environment variable (run `php artisan key:generate --show` locally and paste the value),
   and set `APP_URL` to your Render URL.

> Note: Render's filesystem is ephemeral, so uploaded images are reset on redeploy. For permanent uploads,
> attach object storage (e.g. S3) and switch `FILESYSTEM_DISK`.

---

## Project structure (key folders)

```
app/
  Http/Controllers/      # PostController, CategoryController, CommentController, ProfileController, Auth/, Api/
  Http/Middleware/        # EnsureUserIsAdmin
  Http/Resources/         # PostResource (API transformer)
  Models/                 # User, Profile, Category, Post, Tag, Comment
  Policies/               # PostPolicy
database/
  migrations/             # schema
  seeders/                # demo data
resources/views/          # Blade templates (layouts, partials, components, pages)
routes/
  web.php                 # web routes
  api.php                 # JSON API routes
```
