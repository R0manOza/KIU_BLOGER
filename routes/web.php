<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

// Public author pages.
Route::get('/authors/{user}', [ProfileController::class, 'show'])->name('profile.show');

// Public single-event page (auth enforced in-controller for everything else).
Route::get('/events/{event}', [EventController::class, 'show'])
    ->whereNumber('event')->name('events.show');

/*
|--------------------------------------------------------------------------
| Guest-only authentication routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Posts — full resource CRUD.
| index & show are public; create/store/edit/update/destroy require auth
| (enforced by the controller's middleware() method).
|--------------------------------------------------------------------------
*/
Route::resource('posts', PostController::class);

/*
|--------------------------------------------------------------------------
| Authenticated user routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Follow / unfollow another author.
    Route::post('/authors/{user}/follow', [FollowController::class, 'store'])->name('follow.store');
    Route::delete('/authors/{user}/follow', [FollowController::class, 'destroy'])->name('follow.destroy');
    Route::post('/authors/{user}/follow-events', [FollowController::class, 'toggleEvents'])->name('follow.events');

    // Upvote / downvote a post.
    Route::post('/posts/{post}/vote', [VoteController::class, 'store'])->name('posts.vote');

    // Calendar & events.
    Route::get('/calendar', [EventController::class, 'index'])->name('events.index');
    Route::get('/calendar/feed', [EventController::class, 'feed'])->name('events.feed');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->whereNumber('event')->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->whereNumber('event')->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->whereNumber('event')->name('events.destroy');
    Route::post('/events/{event}/subscribe', [EventController::class, 'subscribe'])->whereNumber('event')->name('events.subscribe');
    Route::delete('/events/{event}/subscribe', [EventController::class, 'unsubscribe'])->whereNumber('event')->name('events.unsubscribe');

    // Notifications.
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
});

/*
|--------------------------------------------------------------------------
| Admin-only routes (protected by the custom "admin" middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('categories', CategoryController::class)->except(['show']);
});
