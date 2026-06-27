@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="flex items-center justify-between">
            <h1 class="font-serif text-2xl font-bold text-slate-900">Notifications</h1>
            @if (auth()->user()->unreadNotifications()->count() > 0)
                <form method="POST" action="{{ route('notifications.readAll') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        <div class="mt-6 space-y-2">
            @forelse ($notifications as $note)
                <a href="{{ route('notifications.read', $note->id) }}"
                   class="flex items-start gap-3 rounded-xl border px-4 py-3 transition hover:shadow-sm
                   {{ $note->read_at ? 'border-slate-200 bg-white' : 'border-brand-200 bg-brand-50/60' }}">
                    <span class="mt-0.5 flex h-8 w-8 flex-none items-center justify-center rounded-full bg-brand-100 text-brand-700">
                        @if (($note->data['icon'] ?? '') === 'event')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0V11.25A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                        @endif
                    </span>
                    <div class="flex-1">
                        <p class="text-sm {{ $note->read_at ? 'text-slate-600' : 'font-semibold text-slate-900' }}">
                            {{ $note->data['message'] ?? 'Notification' }}
                        </p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ $note->created_at->diffForHumans() }}</p>
                    </div>
                    @unless ($note->read_at)
                        <span class="mt-1 h-2 w-2 flex-none rounded-full bg-brand-500"></span>
                    @endunless
                </a>
            @empty
                <p class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400">
                    You have no notifications yet. Follow some authors to get updates!
                </p>
            @endforelse
        </div>

        <div class="mt-6">{{ $notifications->links() }}</div>
    </div>
@endsection
