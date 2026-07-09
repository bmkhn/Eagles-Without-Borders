<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('Renew Membership') }} - {{ config('app.name', 'Eagles Without Borders') }}</title>

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
            .renew-card {
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

                    <div class="flex items-center gap-6">
                        <a href="/" class="text-sm text-white/70 hover:text-white transition-colors inline-flex items-center gap-1">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Home
                        </a>

                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm text-white/70 hover:text-white transition-colors">
                                Dashboard
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <section class="flex-1 flex items-center justify-center py-16">
            <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <div class="renew-card text-center">
                    <!-- Icon -->
                    <div class="inline-flex items-center justify-center size-20 rounded-full bg-amber-500/10 border border-amber-500/20 mb-6">
                        <svg class="size-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-black tracking-tight mb-4">
                        {{ __('Membership') }}
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-600">{{ __('Renewal') }}</span>
                    </h1>

                    <p class="text-gray-400 text-base sm:text-lg leading-relaxed mb-2">
                        {{ __('The profile you\'re looking for belongs to a member whose') }}
                        <span class="text-amber-400 font-semibold">{{ __('membership is inactive') }}</span>.
                    </p>

                    <p class="text-gray-500 text-sm leading-relaxed mb-6">
                        {{ __('Please contact your local club officers or the member directly for more information.') }}
                    </p>

                    {{-- Certificates --}}
                    @if($member->certificates && $member->certificates->count() > 0)
                        <div class="mb-8 text-left border border-white/10 rounded-xl p-4 bg-white/[0.03]">
                            <h3 class="text-sm font-semibold text-gray-300 mb-3 flex items-center gap-2">
                                <svg class="size-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                {{ __('Certificates & Awards') }}
                                <span class="text-xs text-gray-500 font-normal">({{ $member->certificates->count() }})</span>
                            </h3>
                            <div class="space-y-1.5">
                                @foreach($member->certificates as $certificate)
                                    <div class="flex items-center gap-2 py-1.5 px-2 rounded-lg hover:bg-white/[0.05] transition-colors">
                                        <svg class="size-3.5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-sm text-gray-400 truncate flex-1">{{ $certificate->name }}</span>
                                        @if($certificate->file_url)
                                            <a
                                                href="{{ $certificate->file_url }}"
                                                download
                                                class="shrink-0 text-gray-500 hover:text-amber-400 transition-colors"
                                                title="{{ __('Download') }}"
                                            >
                                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <a
                            href="/"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold text-sm transition-all hover:shadow-xl hover:shadow-amber-500/25"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            {{ __('Go Home') }}
                        </a>

                        @auth
                            <a
                                href="{{ url('/dashboard') }}"
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 text-white font-semibold text-sm transition-all"
                            >
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                {{ __('Dashboard') }}
                            </a>
                        @endauth
                    </div>
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
