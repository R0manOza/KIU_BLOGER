# KIU Blogger — Presentation Build Guide (Slide by Slide)

This is your **deck blueprint**. For every slide you get: the title, exactly what to put on it,
and how to design it. The matching spoken lines live in `presentation-script.md`.

> Pair this with the speaker script: open both side by side. This file = *what's on screen*.
> The script file = *what comes out of your mouth*.

---

## Before you start — the design system

Pick ONE tool and stick to it: **PowerPoint**, **Google Slides**, or **Canva** (Canva has the nicest
free templates). Set these up once and reuse on every slide:

| Element | Choice | Why |
|---|---|---|
| Theme | Clean, light background (white / very light grey) | Code and diagrams read better on light backgrounds in a classroom projector |
| Accent color | Deep blue `#0D47A1` (this is the app's actual brand color) | Ties slides to the live site |
| Heading font | A bold serif (e.g. *Playfair Display* or *Georgia*) | Matches the blog's serif headings |
| Body font | A clean sans-serif (e.g. *Inter*, *Calibri*, *Arial*) | Readability |
| Footer | Small text: "KIU Blogger · [Your Name]" + slide number | Looks polished |

**Golden rules**
- Max ~5 bullet points per slide, max ~6 words per bullet. You talk; the slide just anchors.
- Use **real screenshots** of your running site, not stock images.
- When you show code, show a **small snippet** (5–12 lines), syntax-highlighted, never a whole file.
- Total target: **~14 slides, ~10–12 minutes talking + a live demo.**

**Screenshots to capture now** (from your live Render site or `php artisan serve`):
1. Home page (hero + stats + latest posts)
2. Blog index with the category filter/search
3. A single post page (showing the upvote/downvote widget)
4. The "create post" form (showing the image upload + tags)
5. A profile page (showing follower counts + Follow button)
6. The JSON API response at `/api/posts` (browser or screenshot of JSON)
7. Render dashboard showing the live service (green/"Live")

---

## Slide 1 — Title

**On the slide:**
- Big title: **KIU Blogger**
- Subtitle: *A full-stack blog platform built with Laravel*
- Your name, course name, date
- Small line: the live URL (your Render link)

**Design:** Deep-blue background, white text, the "KIU" logo block from the navbar if you can recreate
it (white "K" on a blue rounded square). Keep it minimal — this is the only dark slide.

---

## Slide 2 — What is KIU Blogger?

**On the slide:**
- One-sentence definition: *"A community blog where KIU students publish posts, organise them by
  category and tag, and discuss them in the comments."*
- 3–4 capability bullets: Write & manage posts · Comment & discuss · Follow authors · Up/downvote posts
- A screenshot of the **home page** on the right half.

**Design:** Split layout — text left, screenshot right. This sets the "what" before the "how".

---

## Slide 3 — Technology Stack

**On the slide:** a simple grid/table of logos or labels:

| Layer | Technology |
|---|---|
| Framework | Laravel 13 (PHP 8.4+) |
| Database | SQLite (dev) → PostgreSQL (production) |
| Views | Blade templating + Tailwind CSS |
| Auth | Laravel session authentication |
| Deployment | Docker → Render (auto-deploy from GitHub) |

**Design:** Two columns or a clean table. Add small official logos (Laravel, PHP, PostgreSQL, Docker,
Tailwind) if you have time — it looks professional.

---

## Slide 4 — Architecture: MVC

**On the slide:**
- The request-lifecycle flow as a left-to-right diagram:
  **Browser → Route → Controller → Model (Eloquent) → Database**, then
  **Controller → Blade View → HTML back to Browser**
- One caption: *"Every request follows the same predictable path."*

**Design:** Build the arrow diagram with simple boxes (5 boxes + arrows). Color the boxes in the blue
accent. This is your "I understand the framework" slide — keep it visual, not wordy.

**Optional tiny code anchor** (a single route line):

```php
Route::resource('posts', PostController::class);
```

---

## Slide 5 — Database & Migrations

**On the slide:**
- Bullet: *"Schema is defined in code via migrations — version-controlled and reproducible."*
- Bullet: *"8 tables: users, profiles, categories, posts, tags, comments, follows, votes."*
- A small migration snippet:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('body');
    $table->boolean('is_published')->default(false);
    $table->timestamps();
});
```

**Design:** Code snippet on one side, the table list on the other. Mention the command
`php artisan migrate` in the script.

---

## Slide 6 — Eloquent Relationships  ⭐ (your strongest slide)

**On the slide:** a clean table mapping each relationship type to your app:

| Relationship | In KIU Blogger | Example |
|---|---|---|
| One-to-One | User ↔ Profile | each user has one profile |
| One-to-Many | User → Posts, Post → Comments | an author has many posts |
| Many-to-Many | Post ↔ Tags | posts share tags via a pivot table |
| Many-to-Many (self) | User ↔ User (follows) | a user follows many users |

- One code snippet showing two relationship methods:

```php
public function posts(): HasMany          // One-to-Many
{
    return $this->hasMany(Post::class);
}

public function following(): BelongsToMany // self-referential Many-to-Many
{
    return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id');
}
```

**Design:** The table is the hero. Highlight the last row (self-referential) in the accent color —
that's the "advanced" point the professor will appreciate. Spend the most time here.

---

## Slide 7 — CRUD Operations

**On the slide:**
- The four operations mapped to HTTP + route, in a table:

| Action | Method + URL | Controller method |
|---|---|---|
| Create | `GET/POST /posts/create` | `create()` / `store()` |
| Read | `GET /posts`, `GET /posts/{slug}` | `index()` / `show()` |
| Update | `GET/PUT /posts/{slug}/edit` | `edit()` / `update()` |
| Delete | `DELETE /posts/{slug}` | `destroy()` |

- Caption: *"One resource controller handles the full lifecycle of a post."*

**Design:** Table + a screenshot of the create-post form. Mention comments & categories are also CRUD.

---

## Slide 8 — Validation, CSRF & File Uploads

**On the slide:** three mini-sections with one line each + a snippet.
- **Validation** — server-side rules reject bad input:

```php
$request->validate([
    'title' => ['required', 'string', 'max:255'],
    'body'  => ['required', 'string', 'min:20'],
    'cover_image' => ['nullable', 'image', 'max:4096'],
]);
```

- **CSRF** — every form has `@csrf`; Laravel blocks forged requests.
- **File uploads** — cover images & avatars stored via `Storage`, served from `/storage`.

**Design:** Three labeled rows. Add a small screenshot of a validation error message in red if you have one.

---

## Slide 9 — Authentication & Authorization

**On the slide:** make the distinction crisp (professors love this):
- **Authentication** = *who are you?* → register / login / logout (session-based).
- **Authorization** = *what are you allowed to do?* → middleware + policies.
- Bullets:
  - `auth` middleware protects writing/editing routes
  - Custom `admin` middleware guards category management
  - `PostPolicy`: only the **author** or an **admin** can edit/delete a post

```php
// PostPolicy
public function update(User $user, Post $post): bool
{
    return $user->id === $post->user_id || $user->is_admin;
}
```

**Design:** Two columns — "Authentication" vs "Authorization" — then the policy snippet underneath.

---

## Slide 10 — Blade Templating & UI

**On the slide:**
- Bullets: Master layout · Reusable partials (navbar, footer, flash) · Components (`<x-post-card>`) ·
  Control directives (`@auth`, `@foreach`, `@if`)
- A tiny Blade snippet:

```blade
@foreach ($posts as $post)
    <x-post-card :post="$post" />
@endforeach
```

- A screenshot of the blog index grid (shows the cards rendering).

**Design:** Snippet + screenshot side by side. Point out the UI is responsive (Tailwind).

---

## Slide 11 — JSON API

**On the slide:**
- Bullet: *"A read-only JSON API exposes posts for other apps."*
- Endpoints: `GET /api/posts` and `GET /api/posts/{post}`
- Caption: *"API Resources transform models into clean JSON."*
- A screenshot of the actual JSON output in the browser.

**Design:** Screenshot of the JSON is the hero here. It proves the API is real and working.

---

## Slide 12 — Bonus Features: Follows & Votes

**On the slide:**
- *"Beyond the brief — to make the platform feel real:"*
- **Follow system** — follow authors, see follower/following counts (self-referential many-to-many).
- **Up/down votes** — one vote per user per post; score = sum of votes; toggle to remove.
- Two screenshots: the Follow button + counts, and the upvote/downvote widget.

**Design:** Two feature cards side by side, each with its screenshot. Frame these as "I went further."

---

## Slide 13 — Deployment

**On the slide:**
- Flow: **GitHub push → Render auto-build (Docker) → migrations run → Live**
- Bullets:
  - Containerised with a **Dockerfile** (PHP 8.4 + Apache)
  - **PostgreSQL** managed database on Render
  - Migrations run automatically on every deploy
  - HTTPS enforced in production
- A screenshot of the Render dashboard ("Live").

**Design:** A small horizontal pipeline diagram + the dashboard screenshot.

---

## Slide 14 — Live Demo  🔴

**On the slide (keep it minimal — you'll be on the website):**
- Title: **Live Demo**
- A short checklist of what you'll show (so you don't forget under pressure):
  1. Browse posts → open one
  2. Register / log in
  3. Create a post (with image + tags)
  4. Comment + upvote
  5. Follow an author → show follower count
  6. Admin: manage a category
  7. Hit `/api/posts`

**Design:** Just the title + checklist. Have the **live site already open in another tab** before you start.

---

## Slide 15 — Challenges, Learnings & Thank You

**On the slide:**
- *What I learned:* MVC structure, Eloquent relationships, the deploy pipeline.
- *A challenge I solved:* (pick one real one — e.g. *"getting HTTPS right behind Render's proxy"* or
  *"matching the PHP version in Docker to the locked dependencies"*).
- Big **Thank you** + "Questions?" + the live URL again.

**Design:** Back to the dark blue title-style slide to bookend the deck. End strong.

---

## Appendix — quick facts to have memorized

- Admin login: `admin@kiu.edu.ge` / `password` (demo accounts all use `password`).
- Stack: Laravel 13, PHP 8.4+, PostgreSQL in prod / SQLite in dev, Tailwind, Docker, Render.
- 8 tables, 4 relationship types (incl. one self-referential).
- For deeper answers to likely questions, read `docs/09-exam-prep-qa.md`.
