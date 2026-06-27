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
                    <a href="{{ route('events.index') }}" class="hover:text-brand-600 transition">Calendar</a>
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('categories.index') }}" class="hover:text-brand-600 transition">Categories</a>
                    @endif
                @endauth
            </div>

            <div class="flex items-center gap-3">
                @auth
                    @php($unreadCount = auth()->user()->unreadNotifications()->count())
                    {{-- Notification bell --}}
                    <div class="relative group">
                        <a href="{{ route('notifications.index') }}" class="relative flex h-9 w-9 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100 hover:text-brand-600 transition" aria-label="Notifications">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                            @if ($unreadCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                            @endif
                        </a>
                        <div class="invisible group-hover:visible opacity-0 group-hover:opacity-100 transition absolute right-0 mt-2 w-80 rounded-xl border border-slate-200 bg-white py-2 shadow-lg z-40">
                            <div class="flex items-center justify-between px-4 py-2 border-b border-slate-100">
                                <span class="text-sm font-semibold text-slate-700">Notifications</span>
                                @if ($unreadCount > 0)
                                    <form method="POST" action="{{ route('notifications.readAll') }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium text-brand-600 hover:underline">Mark all read</button>
                                    </form>
                                @endif
                            </div>
                            @forelse (auth()->user()->notifications()->latest()->take(6)->get() as $note)
                                <a href="{{ route('notifications.read', $note->id) }}"
                                   class="block px-4 py-2.5 text-sm hover:bg-slate-50 {{ $note->read_at ? 'text-slate-500' : 'bg-brand-50/60 text-slate-800 font-medium' }}">
                                    {{ $note->data['message'] ?? 'Notification' }}
                                    <span class="mt-0.5 block text-[11px] text-slate-400">{{ $note->created_at->diffForHumans() }}</span>
                                </a>
                            @empty
                                <p class="px-4 py-4 text-center text-sm text-slate-400">No notifications yet.</p>
                            @endforelse
                            <a href="{{ route('notifications.index') }}" class="block border-t border-slate-100 px-4 py-2 text-center text-xs font-medium text-brand-600 hover:underline">View all</a>
                        </div>
                    </div>

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
                            <a href="{{ route('events.index') }}" class="block px-4 py-2 text-sm hover:bg-slate-50">My calendar</a>
                            <a href="{{ route('notifications.index') }}" class="block px-4 py-2 text-sm hover:bg-slate-50">Notifications</a>
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
