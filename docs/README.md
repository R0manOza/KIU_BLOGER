# KIU Blogger — Project Documentation

Welcome! This `docs/` folder is a guided tour of the **KIU Blogger** project. It is written so that
you can read it top-to-bottom and walk away able to explain *every part* of the application — what it
does, how it works, and **why** it was built that way. This is your study guide for the presentation.

## How to read this

Read the chapters in order. Each one builds on the previous. Every chapter follows the same pattern:

1. **The concept** — what the Laravel feature is, in plain language.
2. **Where it lives in our project** — the exact file(s), with line bookmarks.
3. **The code** — annotated snippets.
4. **Why it's done this way** — the reasoning, trade-offs, and what the professor might probe.

> **Bookmarks** look like `app/Models/Post.php:42`. That means *file `app/Models/Post.php`, line 42*.
> Open the file and jump to that line to see the real code in context.

## Table of contents

| # | Chapter | What you'll be able to explain |
|---|---------|--------------------------------|
| 1 | [Overview & Tech Stack](01-overview.md) | What the app is, the theme, and every technology used |
| 2 | [Architecture & Request Lifecycle (MVC)](02-architecture.md) | How a browser request flows through Model–View–Controller |
| 3 | [Database, Migrations & Eloquent](03-database-and-eloquent.md) | The schema, every migration, and all three relationship types |
| 4 | [CRUD, Validation & File Uploads](04-crud-validation-uploads.md) | How create/read/update/delete works end-to-end |
| 5 | [Auth, Middleware & Security](05-auth-middleware-security.md) | Login/registration, route protection, policies, CSRF |
| 6 | [Blade Templating & Frontend](06-blade-and-frontend.md) | Layouts, components, directives, and the UI |
| 7 | [The JSON API](07-api.md) | Resource controllers and API Resources |
| 8 | [Deployment (Docker & Render)](08-deployment.md) | How the app runs in production |
| 9 | [Exam Prep: Q&A + Glossary](09-exam-prep-qa.md) | Practice answers to likely questions |

## 30-second project summary (memorise this)

> *"KIU Blogger is a CRUD-based blogging platform for KIU students, built with PHP and the Laravel
> framework. It follows the MVC architecture. Users register and log in, write blog posts with cover
> images and tags, organise them into categories, and comment on each other's posts. It demonstrates
> Eloquent ORM with one-to-one, one-to-many and many-to-many relationships, full CRUD with form
> validation and CSRF protection, the Blade templating engine, session-based authentication with
> middleware and authorization policies, file uploads, and a read-only JSON API. It runs on a
> PostgreSQL database and is deployed on Render using Docker."*

## The data model at a glance

```
User ──1:1── Profile
 │ │
 │ └────1:N──── Comment ────N:1──── Post
 └──────1:N──── Post ────N:1──── Category
                  │
                  └────N:M──── Tag   (via the post_tag pivot table)
```

- **One-to-One:** a `User` has one `Profile`.
- **One-to-Many:** a `User` has many `Post`s and many `Comment`s; a `Category` has many `Post`s; a `Post` has many `Comment`s.
- **Many-to-Many:** a `Post` has many `Tag`s and a `Tag` belongs to many `Post`s.

## Demo accounts

| Role  | Email               | Password   |
|-------|---------------------|------------|
| Admin | `admin@kiu.edu.ge`  | `password` |
| User  | `nino@kiu.edu.ge`   | `password` |

The admin can manage categories at `/categories`; regular users cannot.
