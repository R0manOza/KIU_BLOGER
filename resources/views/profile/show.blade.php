@extends('layouts.app')

@section('title', $user->name)

@section('content')
    <div class="mx-auto max-w-4xl">
        {{-- Profile header --}}
        <div class="flex flex-col items-center rounded-2xl border border-slate-200 bg-white p-8 text-center sm:flex-row sm:items-start sm:text-left sm:gap-6">
            <img src="{{ $user->avatarUrl() }}" class="h-24 w-24 rounded-full object-cover ring-4 ring-brand-50" alt="">
            <div class="mt-4 flex-1 sm:mt-0">
                <div class="flex flex-col items-center gap-1 sm:flex-row sm:justify-between">
                    <div>
                        <h1 class="font-serif text-2xl font-bold text-slate-900">{{ $user->name }}</h1>
                        @if ($user->profile?->major)
                            <p class="text-sm text-brand-600 font-medium">{{ $user->profile->major }}</p>
                        @endif
                    </div>
                    @auth
                        @if (auth()->id() === $user->id)
                            <a href="{{ route('profile.edit') }}"
                               class="mt-2 rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50 sm:mt-0">
                                Edit profile
                            </a>
                        @else
                            <div class="mt-2 flex items-center gap-2 sm:mt-0">
                                @if (auth()->user()->isFollowing($user))
                                    <form method="POST" action="{{ route('follow.destroy', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg border border-slate-300 px-4 py-1.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                                            Following
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('follow.store', $user) }}">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-lg bg-brand-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-brand-700 transition">
                                            Follow
                                        </button>
                                    </form>
                                @endif

                                @php($followingEvents = auth()->user()->isFollowingEvents($user))
                                <form method="POST" action="{{ route('follow.events', $user) }}">
                                    @csrf
                                    <button type="submit" title="Auto-add this author's events to your calendar"
                                            class="inline-flex items-center gap-1 rounded-lg border px-3 py-1.5 text-sm font-semibold transition
                                            {{ $followingEvents ? 'border-brand-600 bg-brand-50 text-brand-700' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0V11.25A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                                        {{ $followingEvents ? 'Events on' : 'Follow events' }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>

                {{-- Follower / following counts --}}
                <div class="mt-3 flex items-center justify-center gap-6 text-sm sm:justify-start">
                    <span><span class="font-bold text-slate-900">{{ $user->followers_count }}</span>
                        <span class="text-slate-500">Followers</span></span>
                    <span><span class="font-bold text-slate-900">{{ $user->following_count }}</span>
                        <span class="text-slate-500">Following</span></span>
                </div>
                @if ($user->profile?->bio)
                    <p class="mt-3 text-sm text-slate-600">{{ $user->profile->bio }}</p>
                @endif
                @if ($user->profile?->website)
                    <a href="{{ $user->profile->website }}" target="_blank" rel="noopener"
                       class="mt-2 inline-block text-sm font-medium text-brand-600 hover:underline">
                        {{ $user->profile->website }}
                    </a>
                @endif
            </div>
        </div>

        {{-- Author's posts --}}
        <h2 class="mt-10 font-serif text-xl font-bold text-slate-900">Posts by {{ $user->name }}</h2>

        @if ($posts->isEmpty())
            <p class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400">
                No posts published yet.
            </p>
        @else
            <div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>
            <div class="mt-8">{{ $posts->links() }}</div>
        @endif
    </div>
@endsection
