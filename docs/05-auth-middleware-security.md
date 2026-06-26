# 5. Authentication, Middleware & Security

[← Previous: CRUD](04-crud-validation-uploads.md) · [Back to index](README.md) · [Next: Blade & Frontend →](06-blade-and-frontend.md)

This chapter covers most of the **Security, Validation & Advanced Features** rubric item (10 points):
authentication, middleware, authorization, and CSRF protection.

## 5.1 Authentication vs Authorization (know the difference!)

- **Authentication** = *"Who are you?"* — logging in, proving identity. (Are you a logged-in user?)
- **Authorization** = *"Are you allowed to do this?"* — permissions. (Can you edit *this* post?)

We implement both. A common exam trap is mixing them up.

## 5.2 Authentication — simple, session-based, no OAuth

All auth logic is in one controller: `app/Http/Controllers/Auth/AuthController.php`. We use Laravel's
built-in **`Auth` facade** and the **session guard** — no third-party/OAuth providers. This is
deliberately simple so the mechanism is easy to explain.

### Registration

```php
// app/Http/Controllers/Auth/AuthController.php — register()
public function register(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'confirmed', Password::min(8)],
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),   // password is HASHED, never stored plain
    ]);

    $user->profile()->create();   // create the matching 1:1 profile row

    Auth::login($user);                       // log the new user in
    $request->session()->regenerate();        // new session id (prevents session fixation)

    return redirect()->route('posts.index')->with('success', 'Welcome to KIU Blogger, ' . $user->name . '!');
}
```

Points to explain:
- **`unique:users,email`** stops duplicate accounts.
- **`confirmed`** requires a matching `password_confirmation` field.
- **`Hash::make()`** stores a bcrypt hash, never the real password. (Laravel also auto-hashes via the
  model's `'password' => 'hashed'` cast.) **We never store plaintext passwords.**
- **`session()->regenerate()`** issues a fresh session ID on login — a defence against *session
  fixation* attacks.

### Login

```php
// app/Http/Controllers/Auth/AuthController.php — login()
public function login(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (! Auth::attempt($credentials, $request->boolean('remember'))) {
        return back()->withErrors(['email' => 'These credentials do not match our records.'])
                     ->onlyInput('email');
    }

    $request->session()->regenerate();
    return redirect()->intended(route('posts.index'))->with('success', 'Logged in successfully.');
}
```

- **`Auth::attempt()`** checks the email + hashed password for us and starts the session if correct.
- The second argument enables **"remember me"**.
- **`redirect()->intended()`** sends the user back to the page they originally tried to reach before
  being bounced to login.

### Logout

```php
// app/Http/Controllers/Auth/AuthController.php — logout()
Auth::logout();
$request->session()->invalidate();      // destroy session data
$request->session()->regenerateToken(); // new CSRF token
```

## 5.3 Middleware — filtering requests

**Middleware** is code that runs *before* a request reaches the controller — like a checkpoint. We use
three kinds:

### (a) The built-in `auth` middleware — "must be logged in"

Applied to the post write-actions *inside the controller* via the `HasMiddleware` interface:

```php
// app/Http/Controllers/PostController.php:21
public static function middleware(): array
{
    return [
        new Middleware('auth', except: ['index', 'show']),  // guests can only view
    ];
}
```

So `index` and `show` are public, but `create/store/edit/update/destroy` require login. A logged-out
visitor hitting `/posts/create` is automatically redirected to `/login`.

### (b) The built-in `guest` middleware — "must be logged out"

The login/register pages should not be visible to someone already logged in:

```php
// routes/web.php:26
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    // ...
});
```

### (c) Our custom `admin` middleware — role-based access

This is a middleware we wrote ourselves to protect the category-management area:

```php
// app/Http/Middleware/EnsureUserIsAdmin.php
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->is_admin) {
            abort(403, 'This area is reserved for administrators.');
        }
        return $next($request);   // allowed → continue to the controller
    }
}
```

It is given a short alias in `bootstrap/app.php` and then applied to the admin routes:

```php
// bootstrap/app.php:20
$middleware->alias(['admin' => \App\Http\Middleware\EnsureUserIsAdmin::class]);

// routes/web.php:65
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('categories', CategoryController::class)->except(['show']);
});
```

A non-admin who tries to reach `/categories` gets a **403 Forbidden**. This is the textbook example of
*"utilizing Middleware for access control"*.

## 5.4 Authorization — Policies (owner-only editing)

Middleware answers "is this user logged in / an admin?". But "can this user edit *this specific*
post?" is a per-record question — that's a **Policy**.

```php
// app/Policies/PostPolicy.php
class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->is_admin;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->is_admin;
    }
}
```

The controller calls it with `$this->authorize('update', $post)` (see
`PostController::edit/update/destroy`). If the logged-in user is neither the author nor an admin, the
request is rejected with a 403 before any change happens. The Blade view also uses `@can('update', $post)`
to hide the Edit/Delete buttons from people who aren't allowed to use them.

> Laravel auto-discovers `PostPolicy` because of the naming convention (`Post` model →
> `PostPolicy`), so no manual registration is needed.

## 5.5 CSRF protection

**CSRF (Cross-Site Request Forgery)** is an attack where a malicious site tricks your browser into
submitting a form to our app using your logged-in session. Laravel blocks this by requiring a secret
**CSRF token** on every state-changing request (POST/PUT/DELETE).

Every one of our forms includes the token via the `@csrf` Blade directive:

```blade
<form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
    @csrf
    {{-- ...fields... --}}
</form>
```

`@csrf` outputs a hidden `<input name="_token">`. Laravel's `VerifyCsrfToken` middleware (on by default
for all web routes) compares it to the token in the session; if they don't match, the request is
rejected with a **419** error. If you remove `@csrf` from a form, submitting it fails — that's the proof
it's working.

> The API routes (`routes/api.php`) are **stateless and CSRF-exempt** by design, because they don't use
> cookies/sessions — they're meant for programmatic clients, not browser forms.

## 5.6 Other security measures (quick list)

- **Passwords** are bcrypt-hashed (`Hash::make` + the `hashed` cast).
- **Mass-assignment protection** via `$fillable` on every model (see [Chapter 3](03-database-and-eloquent.md#32-eloquent-orm--what-and-why)).
- **Input validation** on every form (see [Chapter 4](04-crud-validation-uploads.md#45-validation--never-trust-user-input)).
- **Blade auto-escapes** output (`{{ $var }}` is HTML-escaped), preventing **XSS** (cross-site scripting).
- **HTTPS forced in production** (`AppServiceProvider::boot()` calls `URL::forceScheme('https')`).

---

[← Previous: CRUD](04-crud-validation-uploads.md) · [Back to index](README.md) · [Next: Blade & Frontend →](06-blade-and-frontend.md)
