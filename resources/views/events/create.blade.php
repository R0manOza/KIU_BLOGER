@extends('layouts.app')

@section('title', 'Create event')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="font-serif text-2xl font-bold text-slate-900">Create an event</h1>
        @if ($post)
            <p class="mt-1 text-sm text-slate-500">Linked to your post: <span class="font-medium text-slate-700">{{ $post->title }}</span></p>
        @endif

        <x-validation-errors class="mt-4" />

        <form method="POST" action="{{ route('events.store') }}" class="mt-6">
            @csrf
            @if ($post)
                <input type="hidden" name="post_id" value="{{ $post->id }}">
            @endif

            @include('events._form')

            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                        class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition">
                    Create event
                </button>
                <a href="{{ route('events.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Cancel</a>
            </div>
        </form>
    </div>
@endsection
