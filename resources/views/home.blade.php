@extends('layouts.app')

@section('title', 'Home')

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 to-brand-900 px-6 py-16 sm:px-12 sm:py-20 text-white">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                Kutaisi International University
            </span>
            <h1 class="mt-4 font-serif text-4xl sm:text-5xl font-bold leading-tight">
                Stories, ideas &amp; voices from the KIU community.
            </h1>
            <p class="mt-4 text-lg text-brand-50/90">
                KIU Blogger is a space for students to publish articles, share campus experiences,
                and discuss everything happening at our university.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('posts.index') }}"
                   class="rounded-lg bg-white px-5 py-3 font-semibold text-brand-700 hover:bg-brand-50 transition">
                    Explore the blog
                </a>
                @guest
                    <a href="{{ route('register') }}"
                       class="rounded-lg border border-white/40 px-5 py-3 font-semibold text-white hover:bg-white/10 transition">
                        Start writing
                    </a>
                @else
                    <a href="{{ route('posts.create') }}"
                       class="rounded-lg border border-white/40 px-5 py-3 font-semibold text-white hover:bg-white/10 transition">
                        Write a post
                    </a>
                @endguest
            </div>
        </div>
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-24 right-24 h-72 w-72 rounded-full bg-white/5"></div>
    </section>

    {{-- Stats --}}
    <section class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center">
            <p class="text-3xl font-extrabold text-brand-600">{{ $postsCount }}</p>
            <p class="mt-1 text-sm text-slate-500">Published posts</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center">
            <p class="text-3xl font-extrabold text-brand-600">{{ $categories->count() }}</p>
            <p class="mt-1 text-sm text-slate-500">Categories</p>
        </div>
        <div class="col-span-2 rounded-2xl border border-slate-200 bg-white p-6 text-center sm:col-span-1">
            <p class="text-3xl font-extrabold text-brand-600">{{ \App\Models\User::count() }}</p>
            <p class="mt-1 text-sm text-slate-500">Community members</p>
        </div>
    </section>

    {{-- Latest posts --}}
    <section class="mt-12">
        <div class="flex items-end justify-between">
            <h2 class="font-serif text-2xl font-bold text-slate-900">Latest posts</h2>
            <a href="{{ route('posts.index') }}" class="text-sm font-semibold text-brand-600 hover:underline">View all &rarr;</a>
        </div>

        @if ($featured->isEmpty())
            <p class="mt-6 rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400">
                No posts have been published yet. Be the first to write one!
            </p>
        @else
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Categories --}}
    @if ($categories->isNotEmpty())
        <section class="mt-12">
            <h2 class="font-serif text-2xl font-bold text-slate-900">Browse by category</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                @foreach ($categories as $category)
                    <a href="{{ route('posts.index', ['category' => $category->slug]) }}"
                       class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:border-brand-300 hover:text-brand-600 transition">
                        {{ $category->name }}
                        <span class="ml-1 text-slate-400">{{ $category->posts_count }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
