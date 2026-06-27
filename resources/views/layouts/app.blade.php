<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KIU Blogger') — KIU Blogger</title>

    {{-- Tailwind CSS (Play CDN) + brand configuration --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef4fb', 100: '#d6e4f5', 500: '#1565C0',
                            600: '#0D47A1', 700: '#0b3c87', 900: '#08285a',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        serif: ['"Source Serif 4"', 'Georgia', 'serif'],
                    },
                },
            },
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        /* Rich-text article rendering */
        .prose-body { line-height: 1.85; }
        .prose-body h1 { font-size: 1.875rem; font-weight: 700; margin: 1.2rem 0 .6rem; }
        .prose-body h2 { font-size: 1.5rem; font-weight: 700; margin: 1.1rem 0 .5rem; }
        .prose-body h3 { font-size: 1.25rem; font-weight: 600; margin: 1rem 0 .4rem; }
        .prose-body p { margin: .75rem 0; }
        .prose-body ul { list-style: disc; padding-left: 1.5rem; margin: .75rem 0; }
        .prose-body ol { list-style: decimal; padding-left: 1.5rem; margin: .75rem 0; }
        .prose-body li { margin: .25rem 0; }
        .prose-body a { color: #0D47A1; text-decoration: underline; }
        .prose-body blockquote { border-left: 4px solid #d6e4f5; padding-left: 1rem; color: #475569; font-style: italic; margin: 1rem 0; }
        .prose-body pre { background: #0f172a; color: #e2e8f0; padding: 1rem; border-radius: .5rem; overflow-x: auto; margin: 1rem 0; }
        .prose-body code { background: #f1f5f9; padding: .1rem .35rem; border-radius: .25rem; font-size: .9em; }
        .prose-body pre code { background: transparent; padding: 0; }
        .prose-body hr { border: 0; border-top: 1px solid #e2e8f0; margin: 1.5rem 0; }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased flex flex-col">
    @include('partials.navbar')

    <main class="flex-1">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8">
            @include('partials.flash')
            @yield('content')
        </div>
    </main>

    @include('partials.footer')
    @stack('scripts')
</body>
</html>
