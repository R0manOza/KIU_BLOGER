@extends('layouts.app')

@section('title', 'Write a post')

@section('content')
    <div class="mx-auto max-w-3xl">
        <h1 class="font-serif text-3xl font-bold text-slate-900">Write a new post</h1>
        <p class="mt-1 text-slate-500">Share your story with the KIU community.</p>

        <x-validation-errors />

        <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data"
              class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
            @csrf
            @include('posts._form', ['categories' => $categories, 'tags' => $tags])

            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                        class="rounded-lg bg-brand-600 px-5 py-2.5 font-semibold text-white hover:bg-brand-700 transition">
                    Publish post
                </button>
                <a href="{{ route('posts.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-800">Cancel</a>
            </div>
        </form>
    </div>
@endsection
