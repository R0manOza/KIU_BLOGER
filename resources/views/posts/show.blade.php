@extends('layouts.app')

@section('title', $post->title)

@section('content')
    <article class="mx-auto max-w-3xl">
        {{-- Header --}}
        <div class="mb-6">
            @if ($post->category)
                <a href="{{ route('posts.index', ['category' => $post->category->slug]) }}"
                   class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">
                    {{ $post->category->name }}
                </a>
            @endif
            <h1 class="mt-3 font-serif text-3xl sm:text-4xl font-bold leading-tight text-slate-900">{{ $post->title }}</h1>

            <div class="mt-4 flex items-center justify-between">
                <a href="{{ route('profile.show', $post->user) }}" class="flex items-center gap-3">
                    <img src="{{ $post->user->avatarUrl() }}" class="h-10 w-10 rounded-full object-cover ring-2 ring-slate-100" alt="">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ $post->user->name }}</p>
                        <p class="text-xs text-slate-400">
                            {{ ($post->published_at ?? $post->created_at)->format('M d, Y') }}
                        </p>
                    </div>
                </a>

                @auth
                    @can('update', $post)
                        <div class="flex items-center gap-2">
                            <a href="{{ route('posts.edit', $post) }}"
                               class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50">Edit</a>
                            <form method="POST" action="{{ route('posts.destroy', $post) }}"
                                  onsubmit="return confirm('Delete this post permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="rounded-lg border border-red-200 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50">Delete</button>
                            </form>
                        </div>
                    @endcan
                @endauth
            </div>
        </div>

        {{-- Voting --}}
        @php($myVote = auth()->check() ? $post->userVote(auth()->user()) : 0)
        <div class="mb-8 flex items-center gap-2">
            @auth
                <form method="POST" action="{{ route('posts.vote', $post) }}">
                    @csrf
                    <input type="hidden" name="value" value="1">
                    <button type="submit" aria-label="Upvote"
                            class="flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm font-semibold transition
                            {{ $myVote === 1 ? 'border-brand-600 bg-brand-50 text-brand-700' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">
                        <span class="text-base leading-none">&#9650;</span> Upvote
                    </button>
                </form>

                <span class="min-w-[2.5rem] text-center text-lg font-bold text-slate-900">{{ $post->score() }}</span>

                <form method="POST" action="{{ route('posts.vote', $post) }}">
                    @csrf
                    <input type="hidden" name="value" value="-1">
                    <button type="submit" aria-label="Downvote"
                            class="flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm font-semibold transition
                            {{ $myVote === -1 ? 'border-red-500 bg-red-50 text-red-600' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">
                        <span class="text-base leading-none">&#9660;</span> Downvote
                    </button>
                </form>
            @else
                <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-500">
                    <span class="text-base font-bold text-slate-700">{{ $post->score() }}</span>
                    <span>points ·</span>
                    <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:underline">Log in to vote</a>
                </div>
            @endauth
        </div>

        {{-- Cover --}}
        @if ($post->coverUrl())
            <img src="{{ $post->coverUrl() }}" alt="{{ $post->title }}"
                 class="mb-8 w-full rounded-2xl object-cover">
        @endif

        {{-- Body --}}
        <div class="prose-body text-lg text-slate-700">{{ $post->body }}</div>

        {{-- Tags --}}
        @if ($post->tags->isNotEmpty())
            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ($post->tags as $tag)
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">#{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif
    </article>

    {{-- Comments --}}
    <section class="mx-auto mt-12 max-w-3xl border-t border-slate-200 pt-8">
        <h2 class="font-serif text-xl font-bold text-slate-900">
            Comments <span class="text-slate-400">({{ $post->comments->count() }})</span>
        </h2>

        @auth
            <form method="POST" action="{{ route('comments.store', $post) }}" class="mt-4">
                @csrf
                <textarea name="body" rows="3" required
                          class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-brand-500 focus:ring-brand-500"
                          placeholder="Share your thoughts...">{{ old('body') }}</textarea>
                @error('body')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <button type="submit"
                        class="mt-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition">
                    Post comment
                </button>
            </form>
        @else
            <p class="mt-4 rounded-xl bg-slate-100 px-4 py-3 text-sm text-slate-500">
                <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:underline">Log in</a> to join the discussion.
            </p>
        @endauth

        <div class="mt-6 space-y-5">
            @forelse ($post->comments as $comment)
                <div class="flex gap-3">
                    <img src="{{ $comment->user->avatarUrl() }}" class="h-9 w-9 rounded-full object-cover" alt="">
                    <div class="flex-1 rounded-xl bg-white border border-slate-200 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-800">{{ $comment->user->name }}</p>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                                @auth
                                    @if (auth()->id() === $comment->user_id || auth()->user()->is_admin)
                                        <form method="POST" action="{{ route('comments.destroy', $comment) }}"
                                              onsubmit="return confirm('Delete this comment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                                        </form>
                                    @endif
                                @endauth
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-slate-600">{{ $comment->body }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-400">No comments yet. Be the first to comment!</p>
            @endforelse
        </div>
    </section>
@endsection
