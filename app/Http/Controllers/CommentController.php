<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Store a new comment authored by the logged-in user on a post.
     */
    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:1000'],
        ]);

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Comment added.');
    }

    /**
     * Delete a comment. Only the comment author or an admin may do this.
     */
    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        abort_unless(
            $request->user()->id === $comment->user_id || $request->user()->is_admin,
            403
        );

        $post = $comment->post;
        $comment->delete();

        return redirect()->route('posts.show', $post)
            ->with('success', 'Comment removed.');
    }
}
