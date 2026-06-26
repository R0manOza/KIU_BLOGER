<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Public author page: shows the user's profile and their posts.
     */
    public function show(User $user): View
    {
        $user->load('profile');
        $posts = $user->posts()->published()->latest()->paginate(6);

        return view('profile.show', compact('user', 'posts'));
    }

    /**
     * Edit form for the currently authenticated user's own profile.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing('profile');

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'major' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user->update(['name' => $validated['name']]);

        $profile = $user->profile()->firstOrCreate();
        $profileData = [
            'major' => $validated['major'] ?? null,
            'website' => $validated['website'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            if ($profile->avatar) {
                Storage::disk('public')->delete($profile->avatar);
            }
            $profileData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->update($profileData);

        return redirect()->route('profile.edit')
            ->with('success', 'Profile updated.');
    }
}
