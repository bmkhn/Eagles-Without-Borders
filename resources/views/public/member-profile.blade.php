<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $member->name }} - {{ config('app.name', 'Eagles Without Borders') }}</title>

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
            .profile-card {
                animation: fadeInUp 0.5s ease-out forwards;
            }
        </style>
    </head>

    <body class="font-sans antialiased bg-gray-950 text-white min-h-screen">
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
        <section class="py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="profile-card">
                    <!-- Profile Card -->
                    <div class="bg-gradient-to-br from-gray-900 to-gray-950 rounded-3xl border border-white/10 overflow-hidden shadow-2xl">
                        <div class="relative h-32 bg-gradient-to-r from-amber-600/20 to-amber-500/10">
                            <div class="absolute -bottom-12 left-8">
                                @if($member->profile_picture_url)
                                    <img
                                        src="{{ $member->profile_picture_url }}"
                                        alt="{{ $member->name }}"
                                        class="size-24 rounded-2xl object-cover shadow-xl border-2 border-gray-950"
                                    >
                                @else
                                    <div class="size-24 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 flex items-center justify-center text-gray-950 font-black text-3xl shadow-xl border-2 border-gray-950">
                                        {{ substr($member->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="pt-16 p-8">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-8">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-1">
                                        <h1 class="text-3xl sm:text-4xl font-bold text-white">
                                            {{ $member->name }}
                                        </h1>

                                        @if($member->status === 'active')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 border border-green-500/20 text-green-400">
                                                <span class="size-1.5 rounded-full bg-green-500"></span>
                                                {{ __('Active') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-500/10 border border-gray-500/20 text-gray-400">
                                                <span class="size-1.5 rounded-full bg-gray-400"></span>
                                                {{ __('Inactive') }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($member->position)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-sm font-medium">
                                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $member->position->name }}
                                        </span>
                                    @endif

                                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="bg-white/5 rounded-xl p-4 border border-white/5">
                                            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-1">{{ __('Club') }}</p>
                                            <p class="text-white font-semibold">{{ optional($member->club)->name ?? '-' }}</p>
                                        </div>

                                        <div class="bg-white/5 rounded-xl p-4 border border-white/5">
                                            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-1">{{ __('Contact Number') }}</p>
                                            <p class="text-white font-semibold">{{ $member->contact_number ?? '-' }}</p>
                                        </div>
                                    </div>

                                    @if($member->club && $member->club->region)
                                        <div class="mt-4 bg-white/5 rounded-xl p-4 border border-white/5">
                                            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-1">{{ __('Region') }}</p>
                                            <p class="text-white font-semibold">{{ $member->club->region->name }}</p>
                                        </div>
                                    @endif

                                    {{-- Payments Section --}}
                                    @php
                                        $paidYears = $member->payments ? $member->payments->pluck('year_paid')->sort()->values()->toArray() : [];
                                    @endphp
                                    @if(!empty($paidYears))
                                        <div class="mt-8">
                                            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                                <svg class="size-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                                {{ __('Membership Years') }}
                                            </h2>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($paidYears as $year)
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 text-sm font-semibold">
                                                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        {{ $year }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if($member->certificates && $member->certificates->count() > 0)
                                        <div class="mt-8">
                                            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                                <svg class="size-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                </svg>
                                                {{ __('Certificates & Awards') }}
                                            </h2>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                @foreach($member->certificates as $certificate)
                                                    <div class="bg-white/5 rounded-lg px-4 py-3 border border-white/5 flex items-center gap-3 group hover:bg-white/[0.07] transition-colors">
                                                        <div class="size-8 rounded-full bg-amber-500/10 flex items-center justify-center shrink-0">
                                                            <svg class="size-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm text-white font-medium truncate">{{ $certificate->name }}</p>
                                                            @if($certificate->issued_at)
                                                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($certificate->issued_at)->format('M d, Y') }}</p>
                                                            @endif
                                                        </div>
                                                        @if($certificate->file_url)
                                                            <a
                                                                href="{{ $certificate->file_url }}"
                                                                download
                                                                class="shrink-0 size-8 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:text-amber-400 hover:bg-amber-500/10 transition-all"
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
                                </div>
                            </div>
                        </div>
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
