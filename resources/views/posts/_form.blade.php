{{--
    Shared create/edit form. Included via @include with:
      $post       (optional) existing post when editing
      $categories collection of categories
      $tags       collection of tags
--}}
@php($editing = isset($post))

<div class="space-y-5">
    {{-- Title --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700">Title</label>
        <input type="text" name="title" value="{{ old('title', $editing ? $post->title : '') }}" required
               class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-brand-500 focus:ring-brand-500">
        @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Category --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700">Category</label>
        <select name="category_id"
                class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-brand-500 focus:ring-brand-500">
            <option value="">— None —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"
                    @selected((string) old('category_id', $editing ? $post->category_id : '') === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Excerpt --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700">Excerpt <span class="font-normal text-slate-400">(short summary, optional)</span></label>
        <textarea name="excerpt" rows="2"
                  class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-brand-500 focus:ring-brand-500">{{ old('excerpt', $editing ? $post->excerpt : '') }}</textarea>
        @error('excerpt')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Body (rich text via Trix) --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700">Body</label>
        <p class="text-xs text-slate-400">Use the toolbar to add headings, bold, lists, quotes and links.</p>
        <input id="post-body" type="hidden" name="body" value="{{ old('body', $editing ? $post->body : '') }}">
        <trix-editor input="post-body"
                     class="trix-content mt-1 min-h-[18rem] rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-serif focus:border-brand-500"></trix-editor>
        @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Cover image upload --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700">Cover image <span class="font-normal text-slate-400">(jpg, png, webp — max 4MB)</span></label>
        @if ($editing && $post->coverUrl())
            <img src="{{ $post->coverUrl() }}" class="mt-2 h-32 rounded-lg object-cover" alt="current cover">
        @endif
        <input type="file" name="cover_image" accept="image/*"
               class="mt-2 block w-full text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
        @error('cover_image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Tags (many-to-many) --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700">Tags</label>
        @php($selectedTags = old('tags', $editing ? $post->tags->pluck('id')->all() : []))
        <div class="mt-2 flex flex-wrap gap-2">
            @forelse ($tags as $tag)
                <label class="cursor-pointer">
                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="peer sr-only"
                           @checked(in_array($tag->id, $selectedTags))>
                    <span class="inline-flex rounded-full border border-slate-300 px-3 py-1 text-sm text-slate-600 peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700">
                        #{{ $tag->name }}
                    </span>
                </label>
            @empty
                <span class="text-sm text-slate-400">No tags available yet.</span>
            @endforelse
        </div>
    </div>

    {{-- Optional linked event --}}
    @php($linkedEvent = $editing ? $post->events->first() : null)
    <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
        <p class="text-sm font-semibold text-slate-700">Attach an event <span class="font-normal text-slate-400">(optional)</span></p>
        <p class="text-xs text-slate-400">Linking an event lets readers add it to their calendar. Editing it later updates everyone who added it.</p>

        <div class="mt-3 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600">Event title</label>
                <input type="text" name="event_title"
                       value="{{ old('event_title', $linkedEvent?->title) }}"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
                @error('event_title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-600">Starts at</label>
                    <input type="datetime-local" name="event_starts_at"
                           value="{{ old('event_starts_at', $linkedEvent && $linkedEvent->starts_at ? $linkedEvent->starts_at->format('Y-m-d\TH:i') : '') }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('event_starts_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600">Ends at</label>
                    <input type="datetime-local" name="event_ends_at"
                           value="{{ old('event_ends_at', $linkedEvent && $linkedEvent->ends_at ? $linkedEvent->ends_at->format('Y-m-d\TH:i') : '') }}"
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('event_ends_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600">Location</label>
                <input type="text" name="event_location"
                       value="{{ old('event_location', $linkedEvent?->location) }}"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
        </div>
    </div>

    {{-- Published toggle --}}
    <div class="flex items-center gap-2">
        <input type="hidden" name="is_published" value="0">
        <input type="checkbox" name="is_published" value="1" id="is_published"
               class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
               @checked(old('is_published', $editing ? $post->is_published : true))>
        <label for="is_published" class="text-sm text-slate-700">Publish immediately</label>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/trix@2.1.1/dist/trix.css">
    <style>
        trix-editor { line-height: 1.7; }
        trix-toolbar .trix-button-group--file-tools { display: none; } /* no file attachments */
    </style>
@endpush
@push('scripts')
    <script src="https://unpkg.com/trix@2.1.1/dist/trix.umd.min.js"></script>
    <script>
        // Disable drag/drop & pasted file attachments (we have no upload endpoint for them).
        addEventListener('trix-file-accept', function (e) { e.preventDefault(); });
    </script>
@endpush
