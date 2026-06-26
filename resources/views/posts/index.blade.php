@extends('layouts.app')

@section('title', 'Blog')

@section('content')
    <header class="mb-8">
        <h1 class="font-serif text-3xl font-bold text-slate-900">The KIU Blog</h1>
        <p class="mt-1 text-slate-500">Discover articles written by students and staff.</p>
    </header>

    {{-- Search + category filter --}}
    <form method="GET" action="{{ route('posts.index') }}"
          class="mb-8 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts..."
               class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
        <select name="category"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-brand-500 focus:ring-brand-500">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition">
            Search
        </button>
        @if (request('search') || request('category'))
            <a href="{{ route('posts.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-500 hover:text-slate-800">Reset</a>
        @endif
    </form>

    @forelse ($posts as $post)
        @if ($loop->first)
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @endif
        <x-post-card :post="$post" />
        @if ($loop->last)
            </div>
    @endif
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
            <p class="text-slate-400">No posts found. Try a different search or category.</p>
        </div>
    @endforelse

    <div class="mt-8">
        {{ $posts->links() }}
    </div>
@endsection
