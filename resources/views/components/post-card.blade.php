@props(['post'])

<article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
    <a href="{{ route('posts.show', $post) }}" class="block aspect-[16/9] overflow-hidden bg-slate-100">
        @if ($post->coverUrl())
            <img src="{{ $post->coverUrl() }}" alt="{{ $post->title }}"
                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-500 to-brand-700 text-white">
                <span class="text-3xl font-extrabold opacity-80">KIU</span>
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-5">
        @if ($post->category)
            <a href="{{ route('posts.index', ['category' => $post->category->slug]) }}"
               class="mb-2 inline-flex w-fit rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700">
                {{ $post->category->name }}
            </a>
        @endif

        <h3 class="font-serif text-lg font-bold leading-snug text-slate-900">
            <a href="{{ route('posts.show', $post) }}" class="hover:text-brand-600">{{ $post->title }}</a>
        </h3>

        <p class="mt-2 flex-1 text-sm text-slate-500 line-clamp-3">
            {{ $post->excerpt ?: Str::limit(strip_tags($post->body), 120) }}
        </p>

        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 text-xs text-slate-400">
            <div class="flex items-center gap-2">
                <img src="{{ $post->user->avatarUrl() }}" class="h-6 w-6 rounded-full object-cover" alt="">
                <span class="font-medium text-slate-600">{{ $post->user->name }}</span>
            </div>
            <span class="inline-flex items-center gap-1 font-semibold text-slate-500" title="Post score">
                <span class="text-sm leading-none text-brand-600">&#9650;</span>{{ $post->score() }}
            </span>
        </div>
    </div>
</article>
