@extends('layouts.app')

@section('title', 'My Calendar')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-serif text-2xl font-bold text-slate-900">My Calendar</h1>
                <p class="mt-1 text-sm text-slate-500">Events you created and events you added from others.</p>
            </div>
            <a href="{{ route('events.create') }}"
               class="inline-flex w-fit items-center gap-1 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition">
                + New event
            </a>
        </div>

        <div class="mt-4 flex items-center gap-4 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-full" style="background:#0D47A1"></span> Created by me</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-full" style="background:#1565C0"></span> Added from others</span>
        </div>

        <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
            <div id="calendar"></div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        #calendar { --fc-border-color: #e2e8f0; --fc-today-bg-color: #eef4fb; font-size: 0.875rem; }
        .fc .fc-button-primary { background: #0D47A1; border-color: #0D47A1; }
        .fc .fc-button-primary:hover { background: #0b3c87; border-color: #0b3c87; }
        .fc .fc-toolbar-title { font-family: 'Source Serif 4', Georgia, serif; }
        .fc-event { cursor: pointer; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek',
                },
                events: '{{ route('events.feed') }}',
                eventClick: function (info) {
                    if (info.event.url) {
                        info.jsEvent.preventDefault();
                        window.location.href = info.event.url;
                    }
                },
            });
            calendar.render();
        });
    </script>
@endpush
