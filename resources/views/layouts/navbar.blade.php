<nav class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
                    @click="$dispatch('toggle-sidebar')"
                    aria-label="Toggle sidebar"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                                <div class="flex items-center gap-1.5">
                    <span class="text-amber-600 dark:text-amber-400 text-base tracking-tight whitespace-nowrap" style="font-family: 'Brush Script', cursive; line-height: 1;">Eagles</span>
                    <span class="text-gray-500 dark:text-gray-400 font-light text-sm whitespace-nowrap">Without Borders</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
            </div>
        </div>
    </div>
</nav>
