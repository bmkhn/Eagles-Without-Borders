<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('Member Directory') }} - {{ config('app.name', 'Eagles Without Borders') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            html { scroll-behavior: smooth; }
            .member-card {
                transition: all 0.3s ease;
            }
            .member-card:hover {
                transform: translateY(-2px);
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
                        <span class="text-amber-500 font-extrabold text-xl tracking-tight">Eagles</span>
                        <span class="text-white/70 font-light hidden sm:inline">Without Borders</span>
                    </a>

                    <div class="flex items-center gap-6">
                        <a href="/" class="text-sm text-white/70 hover:text-white transition-colors">
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

        <!-- Header -->
        <section class="relative py-16 overflow-hidden">
            <div class="absolute inset-0 -z-10">
                <div class="absolute inset-0 bg-gradient-to-b from-gray-900 via-gray-950 to-black"></div>
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-amber-500/5 rounded-full blur-3xl"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <span class="inline-block text-amber-500 font-semibold text-sm tracking-wider uppercase mb-3">Public Directory</span>
                <h1 class="text-4xl sm:text-5xl font-black tracking-tight mb-4">
                    Member
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-600">Directory</span>
                </h1>
                <p class="text-gray-400 max-w-2xl mx-auto text-lg mb-8">
                    Browse our members organized by region and club.
                </p>

                <!-- Search -->
                <form method="GET" action="{{ route('member.directory') }}" class="max-w-xl mx-auto">
                    <div class="relative">
                        <input
                            type="text"
                            name="q"
                            value="{{ $q }}"
                            placeholder="{{ __('Search by name or contact number...') }}"
                            class="w-full px-5 py-3.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all text-sm"
                        >
                        <button
                            type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-gray-950 text-sm font-semibold transition-all"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Search
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Directory Content -->
        <section class="pb-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @forelse($regions as $region)
                    <!-- Region -->
                    <div class="{{ $loop->first ? 'mt-8 ' : '' }}mb-16 last:mb-0">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="size-3 rounded-full bg-amber-500"></div>
                            <h2 class="text-2xl sm:text-3xl font-bold text-white">
                                {{ $region->name }}
                            </h2>
                            <span class="text-sm text-gray-500">
                                ({{ $region->clubs->sum(fn($c) => $c->members->count()) }} {{ Str::plural('member', $region->clubs->sum(fn($c) => $c->members->count())) }})
                            </span>
                        </div>

                        @forelse($region->clubs as $club)
                            <div class="ml-6 mb-8 last:mb-0">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="size-5 text-amber-500/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <h3 class="text-lg sm:text-xl font-semibold text-amber-400/90">
                                        {{ $club->name }}
                                    </h3>
                                    <span class="text-sm text-gray-500">
                                        ({{ $club->members->count() }} {{ Str::plural('member', $club->members->count()) }})
                                    </span>
                                </div>

                                @if($club->members->count() > 0)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                        @foreach($club->members as $member)
                                            <a
                                                href="{{ route('member.profile', $member->slug) }}"
                                                class="member-card block bg-white/5 backdrop-blur-sm rounded-xl p-5 border border-white/10 hover:border-amber-500/30 hover:bg-white/10 transition-all group"
                                            >
                                                <div class="flex items-start gap-3">
                                                    @if($member->profile_picture_url)
                                                        <img
                                                            src="{{ $member->profile_picture_url }}"
                                                            alt="{{ $member->name }}"
                                                            class="size-10 shrink-0 rounded object-cover border border-amber-500/20"
                                                        >
                                                    @else
                                                        <div class="size-10 shrink-0 rounded bg-gradient-to-br from-amber-500 to-amber-700 flex items-center justify-center text-gray-950 font-bold text-sm">
                                                            {{ substr($member->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-white font-semibold text-sm truncate group-hover:text-amber-400 transition-colors">
                                                            {{ $member->name }}
                                                        </p>
                                                        @if($member->position)
                                                            <p class="text-xs text-gray-500 mt-0.5 truncate">
                                                                {{ $member->position->name }}
                                                            </p>
                                                        @endif
                                                        @if($member->contact_number)
                                                            <p class="text-xs text-gray-600 mt-0.5 truncate">
                                                                {{ $member->contact_number }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <svg class="size-4 text-gray-600 group-hover:text-amber-500 transition-colors shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-600 text-sm ml-2 italic">{{ __('No members in this club.') }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm ml-6">{{ __('No clubs found in this region.') }}</p>
                        @endforelse
                    </div>
                @empty
                    <div class="text-center py-20">
                        <svg class="size-16 mx-auto text-gray-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-gray-400 text-lg font-medium">{{ __('No members found') }}</p>
                        @if($q)
                            <p class="text-gray-600 mt-2">{{ __('Try a different search term.') }}</p>
                            <a href="{{ route('member.directory') }}" class="inline-flex items-center gap-1 mt-4 text-amber-500 hover:text-amber-400 text-sm font-medium transition-colors">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                {{ __('Clear search') }}
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-white/10 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-amber-500 font-extrabold">Eagles</span>
                        <span class="text-white/50 font-light">Without Borders</span>
                    </div>
                    <div class="flex items-center gap-4">
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
