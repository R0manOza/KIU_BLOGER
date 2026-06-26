@extends('layouts.app')

@section('title', 'New category')

@section('content')
    <div class="mx-auto max-w-xl">
        <h1 class="font-serif text-3xl font-bold text-slate-900">New category</h1>

        <x-validation-errors />

        <form method="POST" action="{{ route('categories.store') }}"
              class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-brand-500 focus:ring-brand-500">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Description</label>
                <input type="text" name="description" value="{{ old('description') }}"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-brand-500 focus:ring-brand-500">
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 font-semibold text-white hover:bg-brand-700 transition">Create</button>
                <a href="{{ route('categories.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-800">Cancel</a>
            </div>
        </form>
    </div>
@endsection
