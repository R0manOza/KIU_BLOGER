@extends('layouts.app')

@section('title', 'Edit post')

@section('content')
    <div class="mx-auto max-w-3xl">
        <h1 class="font-serif text-3xl font-bold text-slate-900">Edit post</h1>

        <x-validation-errors />

        <form method="POST" action="{{ route('posts.update', $post) }}" enctype="multipart/form-data"
              class="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
            @csrf
            @method('PUT')
            @include('posts._form', ['post' => $post, 'categories' => $categories, 'tags' => $tags])

            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                        class="rounded-lg bg-brand-600 px-5 py-2.5 font-semibold text-white hover:bg-brand-700 transition">
                    Save changes
                </button>
                <a href="{{ route('posts.show', $post) }}" class="text-sm font-medium text-slate-500 hover:text-slate-800">Cancel</a>
            </div>
        </form>
    </div>
@endsection
