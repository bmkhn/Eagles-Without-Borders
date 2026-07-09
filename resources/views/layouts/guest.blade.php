<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Eagles Without Borders') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <style>
            @font-face {
                font-family: 'Brush Script';
                src: url('/fonts/BrushScriptOpti-Regular.otf') format('opentype');
                font-weight: normal;
                font-style: normal;
                font-display: swap;
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-950">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <!-- Background Effect -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-40 -right-40 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-amber-600/5 rounded-full blur-3xl"></div>
            </div>

            <!-- Logo -->
            <div class="relative mb-8">
                <a href="/" class="flex flex-col items-center gap-3 group">
                            <img src="{{ asset('images/logo.png') }}" alt="Eagles Without Borders" class="h-24 w-auto -mb-2">
                            <div class="text-center">
                                <span class="text-amber-500 text-2xl tracking-tight" style="font-family: 'Brush Script', cursive; padding-right: 0.12em; line-height: 1.1;">Eagles</span>
                                <span class="text-white/40 font-light text-2xl tracking-tight">Without Borders</span>
                            </div>
                </a>
            </div>

            <!-- Card -->
            <div class="relative w-full sm:max-w-md px-4">
                <div class="bg-gradient-to-br from-gray-900 to-gray-950 rounded-3xl border border-white/10 shadow-2xl shadow-black/50 overflow-hidden">
                    <div class="relative h-1.5 bg-gradient-to-r from-amber-600/40 via-amber-500/30 to-transparent"></div>
                    <div class="px-8 py-8">
                        {{ $slot }}
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-8 text-center">
                    <p class="text-sm text-gray-600">
                        &copy; {{ date('Y') }} Eagles Without Borders. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
