<nav class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-extrabold text-lg text-brand-600">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600 text-white">K</span>
                <span>KIU <span class="text-slate-800">Blogger</span></span>
            </a>

            <div class="hidden md:flex items-center gap-6 text-sm font-medium">
                <a href="{{ route('home') }}" class="hover:text-brand-600 transition">Home</a>
                <a href="{{ route('posts.index') }}" class="hover:text-brand-600 transition">Blog</a>
                @auth
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('categories.index') }}" class="hover:text-brand-600 transition">Categories</a>
                    @endif
                @endauth
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('posts.create') }}"
                       class="hidden sm:inline-flex items-center gap-1 rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition">
                        Write
                    </a>
                    <div class="relative group">
                        <button class="flex items-center gap-2 rounded-full focus:outline-none">
                            <img src="{{ auth()->user()->avatarUrl() }}" alt="avatar" class="h-9 w-9 rounded-full object-cover ring-2 ring-slate-200">
                        </button>
                        <div class="invisible group-hover:visible opacity-0 group-hover:opacity-100 transition absolute right-0 mt-2 w-48 rounded-xl border border-slate-200 bg-white py-2 shadow-lg">
                            <div class="px-4 py-2 text-xs text-slate-400">Signed in as<br><span class="text-slate-700 font-semibold">{{ auth()->user()->name }}</span></div>
                            <a href="{{ route('profile.show', auth()->user()) }}" class="block px-4 py-2 text-sm hover:bg-slate-50">My profile</a>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-slate-50">Edit profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Log out</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium hover:text-brand-600 transition">Log in</a>
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition">
                        Get started
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>
