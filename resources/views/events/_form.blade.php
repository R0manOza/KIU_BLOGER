{{--
    Shared create/edit event form.
    $event (optional) when editing.
--}}
@php($editing = isset($event))

<div class="space-y-5">
    <div>
        <label class="block text-sm font-semibold text-slate-700">Event title</label>
        <input type="text" name="title" required
               value="{{ old('title', $editing ? $event->title : '') }}"
               class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-brand-500 focus:ring-brand-500">
        @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-semibold text-slate-700">Starts at</label>
            <input type="datetime-local" name="starts_at" required
                   value="{{ old('starts_at', $editing && $event->starts_at ? $event->starts_at->format('Y-m-d\TH:i') : '') }}"
                   class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-brand-500 focus:ring-brand-500">
            @error('starts_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700">Ends at <span class="font-normal text-slate-400">(optional)</span></label>
            <input type="datetime-local" name="ends_at"
                   value="{{ old('ends_at', $editing && $event->ends_at ? $event->ends_at->format('Y-m-d\TH:i') : '') }}"
                   class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-brand-500 focus:ring-brand-500">
            @error('ends_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700">Location <span class="font-normal text-slate-400">(optional)</span></label>
        <input type="text" name="location"
               value="{{ old('location', $editing ? $event->location : '') }}"
               class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-brand-500 focus:ring-brand-500">
        @error('location')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700">Description <span class="font-normal text-slate-400">(optional)</span></label>
        <textarea name="description" rows="4"
                  class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-brand-500 focus:ring-brand-500">{{ old('description', $editing ? $event->description : '') }}</textarea>
        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700">Colour</label>
        <input type="color" name="color"
               value="{{ old('color', $editing ? $event->color : '#0D47A1') }}"
               class="mt-1 h-10 w-20 cursor-pointer rounded border border-slate-300">
    </div>
</div>
