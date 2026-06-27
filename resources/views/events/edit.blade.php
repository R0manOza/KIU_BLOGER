@extends('layouts.app')

@section('title', 'Edit event')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="font-serif text-2xl font-bold text-slate-900">Edit event</h1>
        <p class="mt-1 text-sm text-slate-500">Saving will update this event for everyone who added it to their calendar.</p>

        <x-validation-errors class="mt-4" />

        <form method="POST" action="{{ route('events.update', $event) }}" class="mt-6">
            @csrf
            @method('PUT')

            @include('events._form')

            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                        class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition">
                    Save changes
                </button>
                <a href="{{ route('events.show', $event) }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Cancel</a>
            </div>
        </form>

        <form method="POST" action="{{ route('events.destroy', $event) }}" class="mt-4 border-t border-slate-200 pt-4"
              onsubmit="return confirm('Delete this event permanently?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-lg border border-red-200 px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50">Delete event</button>
        </form>
    </div>
@endsection
