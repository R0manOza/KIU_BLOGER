# 3. Database, Migrations & Eloquent

[← Previous: Architecture](02-architecture.md) · [Back to index](README.md) · [Next: CRUD →](04-crud-validation-uploads.md)

This chapter covers the **MVC & Database Management** rubric item (10 points): migrations, Eloquent
models, and the three relationship types.

## 3.1 Migrations — what and why

A **migration** is a PHP file that describes a database table. Think of it as *version control for your
database schema*: instead of creating tables by hand in a database tool, you write the structure in
code, and `php artisan migrate` builds it. Anyone who clones the project runs the same command and gets
an identical database.

Our migrations live in `database/migrations/`. Each file has an `up()` method (apply the change) and a
`down()` method (undo it).

### Example: the posts table

```php
// database/migrations/2026_01_01_000003_create_posts_table.php:15
Schema::create('posts', function (Blueprint $table) {
    $table->id();                                                  // bigint primary key
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();        // author (FK)
    $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete(); // category (FK)
    $table->string('title');
    $table->string('slug')->unique();                              // URL-friendly title
    $table->string('excerpt', 500)->nullable();
    $table->longText('body');
    $table->string('cover_image')->nullable();                     // path to uploaded image
    $table->boolean('is_published')->default(true);
    $table->timestamp('published_at')->nullable();
    $table->timestamps();                                          // created_at + updated_at
});
```

Key things to be able to explain:

- **`foreignId('user_id')->constrained()`** creates a foreign key to the `users` table. This is what
  enforces the relationship *at the database level*.
- **`cascadeOnDelete()`** — if a user is deleted, their posts are deleted too.
- **`nullOnDelete()`** on `category_id` — if a category is deleted, its posts survive but become
  uncategorised (the column is set to NULL). This is why `category_id` is `nullable()`.
- **`->unique()`** on `slug` guarantees no two posts share a URL.

### The full list of tables

| Migration file | Table | Purpose |
|----------------|-------|---------|
| `0001_..._create_users_table` | `users` | accounts (name, email, password) |
| `2026_..._000007_add_is_admin_to_users_table` | `users` | adds the `is_admin` flag |
| `2026_..._000001_create_profiles_table` | `profiles` | extended user info (1:1 with users) |
| `2026_..._000002_create_categories_table` | `categories` | post categories |
| `2026_..._000003_create_posts_table` | `posts` | the blog posts |
| `2026_..._000004_create_tags_table` | `tags` | tags |
| `2026_..._000005_create_comments_table` | `comments` | comments on posts |
| `2026_..._000006_create_post_tag_table` | `post_tag` | **pivot** linking posts ↔ tags |

> **Why is `is_admin` a separate migration?** The base `users` table comes from Laravel. We added the
> admin flag in its own migration so the change is explicit and so it applies cleanly to databases that
> were already created — that's exactly the real-world purpose of migrations.

## 3.2 Eloquent ORM — what and why

**Eloquent** is Laravel's **ORM (Object-Relational Mapper)**. It lets you work with database rows as
PHP objects instead of writing SQL. Each table has a corresponding **Model** class.

```php
// Instead of: SELECT * FROM posts WHERE id = 1;
$post = Post::find(1);

// Instead of: INSERT INTO posts (...) VALUES (...);
Post::create(['title' => 'Hello', /* ... */]);

// Reading a related row — no JOIN written by hand:
$post->user->name;   // the author's name
```

A model is tiny — it mostly declares which columns are mass-assignable and how it relates to other models:

```php
// app/Models/Comment.php
class Comment extends Model
{
    protected $fillable = ['user_id', 'post_id', 'body'];   // mass-assignable columns

    public function user()  { return $this->belongsTo(User::class); }
    public function post()  { return $this->belongsTo(Post::class); }
}
```

> **What is `$fillable`?** It's a whitelist of columns that can be set in one go via `create()` or
> `update()`. It protects against *mass-assignment* attacks (a user trying to set, say, `is_admin`
> through a form). Anything not listed is ignored. This is a security talking point.

## 3.3 The three relationship types (the heart of the rubric)

The rubric explicitly requires One-to-One, One-to-Many and Many-to-Many. Here is each one, in our code.

### One-to-One — User ↔ Profile

A user has exactly one profile (avatar, bio, major). The profile belongs to one user.

```php
// app/Models/User.php — the "has one" side
public function profile(): HasOne
{
    return $this->hasOne(Profile::class);
}

// app/Models/Profile.php — the inverse "belongs to" side
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

Enforced in the database by a **unique** foreign key, so a user can't have two profiles:

```php
// database/migrations/2026_01_01_000001_create_profiles_table.php
$table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
```

Usage: `$user->profile->bio` or `$profile->user->name`.

### One-to-Many — User → Posts, Category → Posts, Post → Comments

A user writes *many* posts; each post belongs to *one* user.

```php
// app/Models/User.php — "has many"
public function posts(): HasMany   { return $this->hasMany(Post::class); }
public function comments(): HasMany { return $this->hasMany(Comment::class); }

// app/Models/Post.php — the inverse "belongs to"
public function user(): BelongsTo     { return $this->belongsTo(User::class); }
public function category(): BelongsTo { return $this->belongsTo(Category::class); }
public function comments(): HasMany   { return $this->hasMany(Comment::class)->latest(); }
```

Usage: `$user->posts` (a collection), `$post->user` (one user), `$category->posts`, `$post->comments`.

> Note `->latest()` on `comments()` — relationships can carry query constraints, so comments always come
> back newest-first without the controller having to sort them.

### Many-to-Many — Post ↔ Tag

A post can have many tags, and a tag can be attached to many posts. This needs a **pivot table**
(`post_tag`) holding pairs of `post_id` + `tag_id`.

```php
// app/Models/Post.php
public function tags(): BelongsToMany
{
    return $this->belongsToMany(Tag::class);
}

// app/Models/Tag.php
public function posts(): BelongsToMany
{
    return $this->belongsToMany(Post::class);
}
```

The pivot table:

```php
// database/migrations/2026_01_01_000006_create_post_tag_table.php
Schema::create('post_tag', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
    $table->timestamps();
    $table->unique(['post_id', 'tag_id']);   // a tag can't be added to the same post twice
});
```

> **Why is the table called `post_tag`?** Laravel's convention for a pivot table is the two related
> model names, **singular**, in **alphabetical order**, joined by an underscore. Following the
> convention means we don't have to configure anything extra.

Attaching tags to a post uses `sync()`, which sets the exact list (adding/removing as needed):

```php
// app/Http/Controllers/PostController.php:88
$post->tags()->sync($request->input('tags', []));
```

## 3.4 Extra Eloquent features we use

### Query Scopes — reusable query logic

We only ever want to show *published* posts publicly. Instead of repeating
`where('is_published', true)` everywhere, the model defines a **scope**:

```php
// app/Models/Post.php
public function scopePublished(Builder $query): Builder
{
    return $query->where('is_published', true);
}
```

Now any query can just call `->published()`:

```php
Post::published()->latest()->get();
```

### Model events — auto-generating slugs

Categories and tags auto-create their URL slug from their name using a model event:

```php
// app/Models/Category.php
protected static function booted(): void
{
    static::saving(function (Category $category) {
        if (empty($category->slug)) {
            $category->slug = Str::slug($category->name);
        }
    });
}
```

`Str::slug('Campus Life')` → `campus-life`. This runs automatically every time a category is saved.

### Accessors — derived values

The `User` model has a helper that returns an avatar URL, falling back to an auto-generated avatar:

```php
// app/Models/User.php
public function avatarUrl(): string
{
    if ($this->profile && $this->profile->avatar) {
        return asset('storage/' . $this->profile->avatar);
    }
    return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0D47A1&color=fff';
}
```

### Casts — type conversion

```php
// app/Models/Post.php
protected function casts(): array
{
    return [
        'is_published' => 'boolean',     // stored as 0/1, used as true/false
        'published_at' => 'datetime',    // stored as text, used as a Carbon date object
    ];
}
```

Casts let `$post->published_at->diffForHumans()` ("3 days ago") work, because the raw string is
automatically turned into a date object.

---

[← Previous: Architecture](02-architecture.md) · [Back to index](README.md) · [Next: CRUD →](04-crud-validation-uploads.md)
