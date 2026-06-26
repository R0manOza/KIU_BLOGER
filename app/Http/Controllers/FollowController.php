<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /**
     * Follow the given user.
     */
    public function store(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->with('error', "You can't follow yourself.");
        }

        // syncWithoutDetaching adds the row only if it doesn't already exist.
        $request->user()->following()->syncWithoutDetaching([$user->id]);

        return back()->with('success', "You are now following {$user->name}.");
    }

    /**
     * Unfollow the given user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $request->user()->following()->detach($user->id);

        return back()->with('success', "You unfollowed {$user->name}.");
    }
}
