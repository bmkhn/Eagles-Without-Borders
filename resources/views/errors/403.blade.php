@auth
    {{-- Admin 403 --}}
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Access Denied') }}
            </h2>
        </x-slot>

        <div class="py-16">
            <div class="max-w-lg mx-auto sm:px-6 lg:px-8 text-center">
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-8 sm:p-12">
                    <div class="inline-flex items-center justify-center size-16 rounded-full bg-orange-100 dark:bg-orange-500/10 mb-6">
                        <svg class="size-8 text-orange-600 dark:text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9.364-7.364A9 9 0 1112 3a9 9 0 017.364 4.636z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v4"/>
                        </svg>
                    </div>

                    <h1 class="text-5xl sm:text-6xl font-black text-gray-900 dark:text-white tracking-tight mb-2">
                        403
                    </h1>

                    <p class="text-lg text-gray-500 dark:text-gray-400 mb-2">
                        {{ __('Access Denied') }}
                    </p>

                    <p class="text-sm text-gray-400 dark:text-gray-500 mb-8">
                        {{ __("You don't have permission to access this page.") }}
                    </p>

                    <div class="flex items-center justify-center gap-3">
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            {{ __('Go to Dashboard') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>
@else
    {{-- Public 403 --}}
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">

            <title>{{ __('Access Denied') }} - {{ config('app.name', 'Eagles Without Borders') }}</title>

            <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

            <link rel="preconnect" href="https://fonts.bunny.net">
            <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700,800&display=swap" rel="stylesheet" />

            <style>
                @font-face {
                    font-family: 'Brush Script';
                    src: url('/fonts/BrushScriptOpti-Regular.otf') format('opentype');
                    font-weight: normal;
                    font-style: normal;
                    font-display: swap;
                }
            </style>

            @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
                @vite(['resources/css/app.css', 'resources/js/app.js'])
            @endif

            <style>
                @keyframes fadeInUp {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .error-card {
                    animation: fadeInUp 0.5s ease-out forwards;
                }
            </style>
        </head>

        <body class="font-sans antialiased bg-gray-950 text-white min-h-screen flex flex-col">
            <!-- Navbar -->
            <nav class="sticky top-0 z-50 bg-gray-950/80 backdrop-blur-lg border-b border-white/10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <a href="/" class="flex items-center gap-2">
                            <img src="{{ asset('images/logo.png') }}" alt="" class="h-8 w-auto">
                            <span class="text-amber-500 text-lg sm:text-xl tracking-tight" style="font-family: 'Brush Script', cursive; padding-right: 0.08em; line-height: 1.1;">Eagles</span>
                            <span class="text-white/70 font-light text-lg sm:text-xl tracking-tight whitespace-nowrap">Without Borders</span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <section class="flex-1 flex items-center justify-center py-16">
                <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 w-full">
                    <div class="error-card text-center">
                        <!-- Icon -->
                        <div class="inline-flex items-center justify-center size-20 rounded-full bg-orange-500/10 border border-orange-500/20 mb-6">
                            <svg class="size-10 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9.364-7.364A9 9 0 1112 3a9 9 0 017.364 4.636z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v4"/>
                            </svg>
                        </div>

                        <h1 class="text-5xl sm:text-6xl font-black tracking-tight mb-4">
                            <span class="text-orange-500">403</span>
                        </h1>

                        <p class="text-gray-400 text-lg leading-relaxed mb-2">
                            {{ __('Access Denied') }}
                        </p>

                        <p class="text-gray-500 text-sm leading-relaxed mb-8">
                            {{ __("You don't have permission to view this page.") }}
                        </p>

                        <a
                            href="/"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold text-sm transition-all hover:shadow-xl hover:shadow-amber-500/25"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            {{ __('Go Home') }}
                        </a>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="border-t border-white/10 py-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-amber-500" style="font-family: 'Brush Script', cursive; padding-right: 0.08em; line-height: 1.1;">Eagles</span>
                            <span class="text-white/50 font-light">Without Borders</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <a href="https://www.facebook.com/groups/863084874785698" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-blue-400 transition-colors" title="Facebook Group">
                                <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/>
                                </svg>
                            </a>
                            <img src="{{ asset('images/logo.png') }}" alt="" class="h-6 w-auto opacity-50">
                            <span class="text-gray-700">|</span>
                            <a href="{{ route('login') }}" class="text-xs text-gray-600 hover:text-gray-400 transition-colors">
                                Admin Login
                            </a>
                            <span class="text-gray-700">|</span>
                            <p class="text-sm text-gray-500">
                                &copy; {{ date('Y') }} Eagles Without Borders. All rights reserved.
                            </p>
                        </div>
                    </div>
                </div>
            </footer>
        </body>
    </html>
@endauth
