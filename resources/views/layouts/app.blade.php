<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Admin') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

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

        <script>
            // Prevent flash before Alpine initializes
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        </script>
    </head>

    <body
        class="font-sans antialiased bg-gray-100 dark:bg-gray-950 dark:text-gray-200"
        x-data="{
            sidebarOpen: false,
            sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
            darkMode: localStorage.getItem('darkMode') === 'true'
        }"
        x-init="
            $watch('darkMode', val => {
                localStorage.setItem('darkMode', val);
                document.documentElement.classList.toggle('dark', val);
            });
            $watch('sidebarCollapsed', val => {
                localStorage.setItem('sidebarCollapsed', val);
            });
            if (darkMode) document.documentElement.classList.add('dark');
            window.addEventListener('toggle-sidebar', () => {
                if (window.innerWidth < 1024) {
                    sidebarOpen = !sidebarOpen;
                } else {
                    sidebarCollapsed = !sidebarCollapsed;
                }
            });
        "
        @resize.window="sidebarOpen = false"
    >
        <div class="min-h-screen">
            <div class="flex min-h-screen">
                <!-- Sidebar (Desktop) -->
                <div
                    x-cloak
                    class="hidden lg:block lg:flex-shrink-0 transition-[width] duration-200"
                    :class="sidebarCollapsed ? 'lg:w-16' : 'lg:w-64'"
                >
                    @include('layouts.sidebar')
                </div>

                <!-- Mobile sidebar overlay -->
                <div
                    class="fixed inset-0 z-40 lg:hidden"
                    x-show="sidebarOpen"
                    x-cloak
                    x-transition.opacity
                >
                    <div class="absolute inset-0 bg-black/30" @click="sidebarOpen = false"></div>

                    <div class="relative h-full w-72 max-w-[85vw] bg-white dark:bg-gray-900 shadow-xl overflow-y-auto">
                        @include('layouts.sidebar')
                    </div>
                </div>

                <!-- Content column -->
                <div class="flex flex-1 flex-col min-w-0">
                    @include('layouts.navbar')

                    <!-- Page Heading -->
                    @isset($header)
                        <header class="bg-white dark:bg-gray-900 dark:border-gray-700 shadow">
                            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <main class="flex-1">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>

        <script>
            window.addEventListener('toggle-sidebar', () => {});
            document.addEventListener('DOMContentLoaded', () => {});
        </script>
    </body>
</html>
