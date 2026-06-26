<?php

use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API routes (stateless, JSON responses) — prefixed with /api automatically.
|--------------------------------------------------------------------------
*/
Route::apiResource('posts', PostController::class)
    ->only(['index', 'show'])
    ->names('api.posts');
