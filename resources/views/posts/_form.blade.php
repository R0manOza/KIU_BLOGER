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

    {{-- Body --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700">Body</label>
        <textarea name="body" rows="12" required
                  class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 font-serif focus:border-brand-500 focus:ring-brand-500">{{ old('body', $editing ? $post->body : '') }}</textarea>
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

    {{-- Published toggle --}}
    <div class="flex items-center gap-2">
        <input type="hidden" name="is_published" value="0">
        <input type="checkbox" name="is_published" value="1" id="is_published"
               class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
               @checked(old('is_published', $editing ? $post->is_published : true))>
        <label for="is_published" class="text-sm text-slate-700">Publish immediately</label>
    </div>
</div>
