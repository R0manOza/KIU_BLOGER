<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    /**
     * Cast (or toggle / switch) the current user's vote on a post.
     *
     * - No existing vote      -> create it.
     * - Same value clicked    -> remove it (toggle off).
     * - Different value       -> switch the vote.
     */
    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'value' => ['required', 'integer', 'in:1,-1'],
        ]);
        $value = (int) $validated['value'];
        $user = $request->user();

        $existing = $post->votes()->where('user_id', $user->id)->first();

        if ($existing) {
            if ($existing->value === $value) {
                $existing->delete();
            } else {
                $existing->update(['value' => $value]);
            }
        } else {
            $post->votes()->create([
                'user_id' => $user->id,
                'value' => $value,
            ]);
        }

        return back();
    }
}
