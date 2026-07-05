<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $member = \App\Models\Member::query()
                ->with(['position', 'club.region', 'certificates'])
                ->where('slug', $slug)
                ->firstOrFail();
        @endphp

        <title>{{ $member->name }} - {{ config('app.name', 'Eagles Without Borders') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

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
                        <span class="text-amber-500 font-extrabold text-xl tracking-tight">Eagles</span>
                        <span class="text-white/70 font-light hidden sm:inline">Without Borders</span>
                    </a>

                    <div class="flex items-center gap-6">
                        <a href="{{ route('member.directory') }}" class="text-sm text-white/70 hover:text-white transition-colors inline-flex items-center gap-1">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Directory
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
                    <!-- Back link -->
                    <a href="{{ route('member.directory') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-amber-400 transition-colors mb-8">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        {{ __('Back to Directory') }}
                    </a>

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
                                    <h1 class="text-3xl sm:text-4xl font-bold text-white mb-1">
                                        {{ $member->name }}
                                    </h1>
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

                                    @if($member->certificates && $member->certificates->count() > 0)
                                        <div class="mt-8">
                                            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                                <svg class="size-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                </svg>
                                                {{ __('Certificates') }}
                                            </h2>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                @foreach($member->certificates as $certificate)
                                                    <div class="bg-white/5 rounded-lg px-4 py-3 border border-white/5 flex items-center gap-3">
                                                        <div class="size-8 rounded-full bg-amber-500/10 flex items-center justify-center">
                                                            <svg class="size-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm text-white font-medium">{{ $certificate->name ?? ($certificate->title ?? ('Certificate #' . $certificate->id)) }}</p>
                                                            @if($certificate->issued_at)
                                                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($certificate->issued_at)->format('M d, Y') }}</p>
                                                            @endif
                                                        </div>
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
        <footer class="border-t border-white/10 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-amber-500 font-extrabold">Eagles</span>
                        <span class="text-white/50 font-light">Without Borders</span>
                    </div>
                    <p class="text-sm text-gray-500">
                        &copy; {{ date('Y') }} Eagles Without Borders. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </body>
</html>
