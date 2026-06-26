# 1. Overview & Tech Stack

[← Back to index](README.md) · [Next: Architecture →](02-architecture.md)

## 1.1 What is the project?

**KIU Blogger** is a fully functional, database-backed **blog web application** for the students of
Kutaisi International University. It is a classic content-management style app where:

- Visitors (guests) can **browse** all published posts, read a single post, filter by category, search, and view author profiles.
- Registered users can **write, edit and delete** their own posts (with a cover image and tags), **comment** on any post, and **customise their profile** (avatar, bio, major).
- Administrators can additionally **manage the blog's categories**.

The theme satisfies the syllabus requirement directly: the brief recommended *"a functional website
about KIU"*, and a blog is the canonical example of a CRUD application given in the rubric.

## 1.2 Why a blog?

A blog is the ideal vehicle for demonstrating the course concepts because it naturally contains:

- **Multiple related entities** (users, posts, comments, categories, tags) → perfect for showing all
  three Eloquent relationship types.
- **Obvious CRUD operations** (a post is created, read, updated, deleted).
- **A need for authentication** (you must be logged in to write) and **authorization** (you can only
  edit *your own* posts).
- **File uploads** (cover images, avatars).
- **A natural public API** (a list of posts as JSON).

In other words, every single grading criterion maps onto a real, justified feature — nothing is bolted
on artificially.

## 1.3 The technology stack

| Layer | Technology | Role in the project |
|-------|-----------|---------------------|
| Language | **PHP 8.4+** | The server-side programming language. |
| Framework | **Laravel 13** | The MVC framework that structures the whole app. |
| Templating | **Blade** | Laravel's server-side HTML templating engine. |
| ORM | **Eloquent** | Laravel's Object-Relational Mapper — lets us use PHP objects instead of raw SQL. |
| Database (dev) | **SQLite** | A zero-configuration file database for local development. |
| Database (prod) | **PostgreSQL** | A production-grade relational database (hosted on Render). |
| Styling | **Tailwind CSS** | A utility-first CSS framework for the UI (loaded via CDN). |
| Container | **Docker** | Packages the app + PHP + Apache into one image for deployment. |
| Hosting | **Render** | The cloud platform running the container and the Postgres database. |

### Why these choices?

- **Laravel** is the framework the course is built around, and it bundles routing, ORM, templating,
  validation, authentication and migrations in one coherent package — exactly the concepts being graded.
- **SQLite in development** means anyone can clone the repo and run it with zero database setup (no
  MySQL/Postgres server to install). **Postgres in production** because the hosting filesystem is
  ephemeral, so a managed database is required for data to survive restarts.
- **Tailwind via CDN** keeps the project simple (no Node build step needed to view the site) while
  still producing a clean, modern, professional UI.

## 1.4 Project structure (the folders that matter)

```
KIU_BLOGER/
├── app/
│   ├── Http/
│   │   ├── Controllers/     ← the "C" in MVC: handles requests
│   │   │   ├── Auth/AuthController.php
│   │   │   ├── Api/PostController.php
│   │   │   ├── PostController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── CommentController.php
│   │   │   ├── ProfileController.php
│   │   │   └── HomeController.php
│   │   ├── Middleware/      ← request filters (e.g. admin-only)
│   │   │   └── EnsureUserIsAdmin.php
│   │   └── Resources/       ← API JSON transformers
│   │       └── PostResource.php
│   ├── Models/              ← the "M" in MVC: Eloquent models
│   │   ├── User.php  Profile.php  Post.php  Category.php  Tag.php  Comment.php
│   ├── Policies/            ← authorization rules
│   │   └── PostPolicy.php
│   └── Providers/AppServiceProvider.php
├── bootstrap/app.php        ← app configuration (routing, middleware)
├── database/
│   ├── migrations/          ← database schema definitions
│   └── seeders/             ← demo data
├── resources/views/         ← the "V" in MVC: Blade templates
├── routes/
│   ├── web.php              ← browser (web) routes
│   └── api.php              ← JSON API routes
├── Dockerfile               ← production container definition
└── render.yaml              ← Render deployment blueprint
```

This layout is **Laravel's standard convention**, not something invented for this project. That itself
is a talking point: *"Laravel enforces a conventional structure, which is part of how it implements the
MVC pattern — models, views and controllers each have a dedicated home."*

---

[← Back to index](README.md) · [Next: Architecture →](02-architecture.md)
