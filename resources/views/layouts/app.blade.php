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
        .prose-body { white-space: pre-line; line-height: 1.85; }
    </style>
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
</body>
</html>
