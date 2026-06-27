@extends('layouts.app')

@section('title', $event->title)

@section('content')
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('events.index') }}" class="text-sm font-medium text-brand-600 hover:underline">&larr; Back to calendar</a>

        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="h-2" style="background: {{ $event->color }}"></div>
            <div class="p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="font-serif text-2xl font-bold text-slate-900">{{ $event->title }}</h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Created by
                            <a href="{{ route('profile.show', $event->creator) }}" class="font-medium text-brand-600 hover:underline">{{ $event->creator->name }}</a>
                        </p>
                    </div>
                    @auth
                        @if ($event->user_id === auth()->id() || auth()->user()->is_admin)
                            <a href="{{ route('events.edit', $event) }}"
                               class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50">Edit</a>
                        @endif
                    @endauth
                </div>

                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex gap-3">
                        <dt class="w-24 flex-none font-semibold text-slate-500">When</dt>
                        <dd class="text-slate-800">
                            {{ $event->starts_at->format('D, M j, Y · H:i') }}
                            @if ($event->ends_at)
                                &ndash; {{ $event->ends_at->format('H:i') }}
                            @endif
                        </dd>
                    </div>
                    @if ($event->location)
                        <div class="flex gap-3">
                            <dt class="w-24 flex-none font-semibold text-slate-500">Where</dt>
                            <dd class="text-slate-800">{{ $event->location }}</dd>
                        </div>
                    @endif
                    @if ($event->post)
                        <div class="flex gap-3">
                            <dt class="w-24 flex-none font-semibold text-slate-500">Post</dt>
                            <dd><a href="{{ route('posts.show', $event->post) }}" class="text-brand-600 hover:underline">{{ $event->post->title }}</a></dd>
                        </div>
                    @endif
                </dl>

                @if ($event->description)
                    <p class="mt-6 whitespace-pre-line text-slate-700">{{ $event->description }}</p>
                @endif

                {{-- Add / remove from calendar --}}
                @auth
                    @if ($event->user_id !== auth()->id())
                        <div class="mt-8 border-t border-slate-100 pt-6">
                            @if ($event->isSubscribedBy(auth()->user()))
                                <form method="POST" action="{{ route('events.unsubscribe', $event) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                                        On your calendar — remove
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('events.subscribe', $event) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition">
                                        + Add to my calendar
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="mt-8 border-t border-slate-100 pt-6">
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-brand-600 hover:underline">Log in</a>
                        <span class="text-sm text-slate-500">to add this event to your calendar.</span>
                    </div>
                @endauth
            </div>
        </div>
    </div>
@endsection
