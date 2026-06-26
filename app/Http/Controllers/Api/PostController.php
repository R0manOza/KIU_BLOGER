<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Read-only JSON API for blog posts.
 * Demonstrates Resource Controllers and API Resources returning JSON responses.
 */
class PostController extends Controller
{
    /**
     * GET /api/posts — paginated collection of published posts.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $posts = Post::with(['user', 'category', 'tags'])
            ->withCount('comments')
            ->published()
            ->latest()
            ->paginate(10);

        return PostResource::collection($posts);
    }

    /**
     * GET /api/posts/{post} — a single published post.
     */
    public function show(Post $post): PostResource
    {
        $post->load(['user', 'category', 'tags'])->loadCount('comments');

        return new PostResource($post);
    }
}
