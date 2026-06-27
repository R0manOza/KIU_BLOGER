# KIU Blogger — Speaker Script

This is **what you say**, slide by slide. It pairs with `presentation-slides.md` (what's on screen).

**How to use it**
- Read it out loud 2–3 times before the presentation. Don't memorize word-for-word — learn the *flow*.
- `[SLIDE N]` tells you when to advance. `(action)` notes are stage directions, not spoken.
- Rough timing is marked per slide. Total talking ≈ 10–11 min + ~3–4 min demo.
- Speak slowly. Pause after each slide change. It always feels faster to the audience than to you.

---

### [SLIDE 1 — Title]  ~20 sec

"Hi everyone. My project is called **KIU Blogger** — it's a full-stack blog platform I built with the
Laravel framework. It's fully deployed and live on the internet, and by the end I'll show it to you
running in real time. Let me start with what it actually is."

---

### [SLIDE 2 — What is KIU Blogger?]  ~45 sec

"KIU Blogger is a community blog for KIU students. The idea is simple: a student can register, write
posts, organise them into categories and tags, and the community can read, comment, follow each other,
and upvote the posts they like.

So it's not just a demo with one page — it's a complete content platform with real users, real content,
and real interaction. Everything you'd expect from a small social blog."

---

### [SLIDE 3 — Technology Stack]  ~45 sec

"Here's what it's built on. The core is **Laravel 13**, running on **PHP**. For the database I use
**SQLite** while developing locally, and it switches to **PostgreSQL** in production — same code, just a
config change, which is one of Laravel's strengths.

The front end is rendered with **Blade**, Laravel's templating engine, styled with **Tailwind CSS**. And
the whole thing is containerised with **Docker** and deployed to **Render**, which automatically rebuilds
every time I push to GitHub."

---

### [SLIDE 4 — Architecture: MVC]  ~1 min

"The project follows the **MVC** pattern — Model, View, Controller — and every single request flows the
same way.

A request comes in from the browser. Laravel's **router** matches the URL and sends it to the right
**controller** method. The controller talks to the **model** — that's Eloquent — which reads or writes
to the **database**. Then the controller passes the data to a **Blade view**, which renders HTML and
sends it back to the browser.

The benefit is separation of concerns: routing, business logic, data, and presentation each live in
their own place. So the codebase stays organised and easy to extend."

---

### [SLIDE 5 — Database & Migrations]  ~1 min

"The database schema isn't created by hand — it's defined in code using **migrations**. Each migration
is a PHP file describing a table, and they're version-controlled with the project. I just run
`php artisan migrate` and Laravel builds the whole schema.

The big advantage is reproducibility — any teammate, or the production server, can rebuild the exact same
database from scratch. In total there are eight tables.

Here you can see the posts migration — notice the **foreign keys** linking a post to its author and its
category, and the `cascadeOnDelete`, which means if a user is deleted, their posts go with them. That
keeps the data consistent."

---

### [SLIDE 6 — Eloquent Relationships]  ~1 min 30 sec  ⭐ spend time here

"This is the part I'm most proud of — the **Eloquent relationships**, which connect the tables together.
I implemented all the main relationship types.

**One-to-one**: each user has exactly one profile.
**One-to-many**: an author has many posts, and a post has many comments.
**Many-to-many**: posts and tags — a post can have several tags, and a tag belongs to many posts, linked
through a pivot table.

And then a more advanced one — a **self-referential many-to-many**: the follow system. A user follows
many users, and is followed by many users — so it's the users table related *to itself* through a
`follows` pivot table.

In code, a relationship is just a method. `posts()` returns a *has-many*. `following()` returns a
*belongs-to-many* pointing back at the User model. Once these are defined, I can write
`$user->posts` or `$user->following` anywhere and Eloquent handles all the SQL for me."

---

### [SLIDE 7 — CRUD Operations]  ~50 sec

"On top of those models, the app does full **CRUD** — Create, Read, Update, Delete. The main example is
posts.

Laravel gives me a *resource controller*, so one controller cleanly handles the entire lifecycle: a form
to **create** a post, pages to **read** the list and individual posts, a form to **update**, and a
**delete** action. The same pattern is reused for comments and for category management. So CRUD isn't in
one place — it's the backbone of the whole app."

---

### [SLIDE 8 — Validation, CSRF & File Uploads]  ~1 min

"Of course, you can't just trust whatever the user submits, so there are three safeguards here.

First, **validation** — every form submission is checked against rules on the server. The title is
required, the body needs a minimum length, the cover image has to actually be an image under a size
limit. If anything fails, the user is sent back with clear error messages.

Second, **CSRF protection** — every form includes a `@csrf` token, so Laravel can block forged requests
from other sites. It's automatic but important for security.

And third, **file uploads** — users can upload a cover image for a post and an avatar for their profile.
Those files are stored through Laravel's storage system and served back to the browser."

---

### [SLIDE 9 — Authentication & Authorization]  ~1 min

"Next, security — and I want to draw a clear line between two things that sound similar.

**Authentication** is *who are you* — that's the register, login, and logout system, using Laravel's
session-based auth.

**Authorization** is *what are you allowed to do* — and that's handled with **middleware** and
**policies**. The `auth` middleware blocks guests from writing or editing. I also wrote a **custom admin
middleware** that protects category management, so only admins can touch it.

And this snippet is a **policy**: it says you can only edit or delete a post if you're the author of it,
or you're an admin. So users can't tamper with each other's content."

---

### [SLIDE 10 — Blade Templating & UI]  ~50 sec

"The front end is built with **Blade**. I didn't repeat HTML everywhere — I used a master layout that
every page extends, **partials** for shared pieces like the navbar and footer, and reusable
**components** like this `post-card`, which renders a post preview anywhere I need one.

Blade also has control directives — loops, conditionals, and `@auth` to show or hide things depending on
whether someone's logged in. Combined with Tailwind CSS, the result is a clean, responsive interface that
works on mobile too."

---

### [SLIDE 11 — JSON API]  ~40 sec

"Beyond the web pages, the project also exposes a small **JSON API**. There are two endpoints — a list of
posts, and a single post.

This is what another application, or a mobile app, would consume. I use Laravel's **API Resources** to
transform the raw models into a clean, predictable JSON structure — so I control exactly what's exposed.
Here's the actual live response."

---

### [SLIDE 12 — Bonus Features: Follows & Votes]  ~50 sec

"I also added two features beyond the requirements, to make the platform feel real.

The first is the **follow system** — you can follow an author and see their follower and following counts.
This is the self-referential relationship I mentioned earlier, now wired up to a real button.

The second is **upvotes and downvotes** on posts. Each user gets one vote per post, the score is the sum
of all votes, and clicking your vote again removes it. It's a small thing, but it makes the blog feel
interactive — like a real community site."

---

### [SLIDE 13 — Deployment]  ~50 sec

"Finally, the app is genuinely **deployed**, not just running on my laptop.

The flow is fully automated: I push to GitHub, Render builds a **Docker** container, runs the database
**migrations** automatically, and the new version goes live — all by itself. It uses a managed
**PostgreSQL** database, and HTTPS is enforced in production. So this is a real production setup, not just
a local demo."

---

### [SLIDE 14 — Live Demo]  ~3–4 min  🔴

(Switch to the browser tab you already have open. Go slowly and narrate each click.)

"Now let me show it live.

- Here's the home page, and the blog with all the posts. I'll open one… and you can see the content,
  the author, the comments, and the upvote/downvote buttons.
- Let me log in… (log in with your account).
- I'll create a new post — give it a title, a body, attach a cover image, pick a couple of tags, and
  publish. And there it is, live immediately.
- I'll add a comment, and upvote the post — watch the score change.
- Now I'll go to another author's profile and **follow** them — you can see the follower count go up.
- As an admin, I can manage categories here, which a normal user can't access.
- And lastly, the JSON API — here's `/api/posts` returning live data."

(If anything misbehaves, stay calm: "I'll come back to that" and move on. Have screenshots on the slides
as a backup.)

---

### [SLIDE 15 — Challenges, Learnings & Thank You]  ~40 sec

"To wrap up — building this taught me how the pieces of a real web framework fit together: the MVC
structure, Eloquent relationships, and especially the deployment pipeline.

One real challenge was getting HTTPS to work correctly once it was deployed behind Render's proxy — the
app generated insecure links until I configured it to trust the proxy headers. Solving that taught me a
lot about how apps behave differently in production.

That's KIU Blogger — thank you for listening, and I'm happy to take any questions."

---

## Anticipated questions (have these ready)

- **"Why Laravel?"** — It's a mature, batteried-included PHP framework: routing, ORM, auth, validation,
  and templating all built in, which let me focus on features instead of plumbing.
- **"What's the difference between SQLite and PostgreSQL here?"** — SQLite is a simple file-based DB,
  great for local development; PostgreSQL is a robust server database for production. Eloquent abstracts
  the differences so the code doesn't change.
- **"How does a many-to-many relationship work?"** — Through a pivot table that stores pairs of IDs. For
  posts and tags it's the `post_tag` table; for follows it's the `follows` table linking user to user.
- **"How is the password stored?"** — Hashed with bcrypt, never in plain text — Laravel hashes it
  automatically.
- **"What happens if I delete a user?"** — Foreign keys with cascade delete remove their posts, comments,
  votes, and follows, so no orphaned data is left behind.
- **"Is the API secured?"** — It's read-only (only GET endpoints), so it just exposes public post data.

> For a much deeper Q&A bank, see `docs/09-exam-prep-qa.md`.
