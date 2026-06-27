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

    /**
     * Toggle "follow events" for a user you already follow. When on, their new
     * events are auto-added to your calendar and you get notified.
     */
    public function toggleEvents(Request $request, User $user): RedirectResponse
    {
        $me = $request->user();

        if (! $me->isFollowing($user)) {
            // Following events implies following the user.
            $me->following()->syncWithoutDetaching([$user->id]);
        }

        $now = $me->isFollowingEvents($user);
        $me->following()->updateExistingPivot($user->id, ['follow_events' => ! $now]);

        return back()->with(
            'success',
            $now
                ? "You'll no longer get {$user->name}'s events on your calendar."
                : "You'll now get {$user->name}'s events on your calendar."
        );
    }
}
