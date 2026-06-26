@extends('layouts.app')

@section('title', 'Edit profile')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="font-serif text-3xl font-bold text-slate-900">Edit your profile</h1>

        <x-validation-errors />

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
              class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-4">
                <img src="{{ $user->avatarUrl() }}" class="h-16 w-16 rounded-full object-cover ring-2 ring-slate-100" alt="">
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-slate-700">Avatar <span class="font-normal text-slate-400">(max 2MB)</span></label>
                    <input type="file" name="avatar" accept="image/*"
                           class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                    @error('avatar')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-brand-500 focus:ring-brand-500">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700">Major / Field of study</label>
                <input type="text" name="major" value="{{ old('major', $user->profile?->major) }}"
                       placeholder="e.g. Computer Science"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-brand-500 focus:ring-brand-500">
                @error('major')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700">Website</label>
                <input type="url" name="website" value="{{ old('website', $user->profile?->website) }}"
                       placeholder="https://"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-brand-500 focus:ring-brand-500">
                @error('website')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700">Bio</label>
                <textarea name="bio" rows="4"
                          class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-brand-500 focus:ring-brand-500">{{ old('bio', $user->profile?->bio) }}</textarea>
                @error('bio')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="rounded-lg bg-brand-600 px-5 py-2.5 font-semibold text-white hover:bg-brand-700 transition">
                Save profile
            </button>
        </form>
    </div>
@endsection
