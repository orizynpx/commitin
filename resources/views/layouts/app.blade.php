<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'COMMITIN')</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased text-on-surface bg-background flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

    <div x-show="sidebarOpen" 
         x-on:click="sidebarOpen = false" 
         class="fixed inset-0 z-30 bg-black/40 md:hidden transition-opacity" 
         x-transition:enter="transition-opacity ease-out duration-300" 
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition-opacity ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0"
         style="display: none;">
    </div>

    <aside class="fixed md:static inset-y-0 left-0 z-40 w-64 bg-surface-container-lowest flex-shrink-0 h-full flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out border-r border-surface-dim" 
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
        <div class="h-16 flex items-center px-6 border-b border-surface-dim justify-between">
            <a href="/" class="flex items-center">
                <x-application-logo class="h-8 w-auto text-primary dark:text-primary-fixed-dim" />
            </a>
            <button x-on:click="sidebarOpen = false" class="md:hidden text-on-surface-variant hover:text-on-surface focus:outline-none">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            @if (auth()->check())
                @if (auth()->user()->role === 'student')
                    <div class="px-3 py-2 text-xs font-semibold text-outline-variant uppercase tracking-wider">
                        {{ __('Dashboard') }}
                    </div>
                    <a href="{{ route('student.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('student.dashboard') ? 'text-primary bg-surface-container font-medium' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Dashboard Pelamar') }}</span>
                    </a>
                    <a href="{{ route('organizer.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('organizer.dashboard') ? 'text-primary bg-surface-container font-medium' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Dashboard Penyelenggara') }}</span>
                    </a>

                    <div class="px-3 py-2 text-xs font-semibold text-outline-variant uppercase tracking-wider">
                        {{ __('Menu') }}
                    </div>
                    <a href="{{ route('vacancies.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('vacancies.index') ? 'text-primary bg-surface-container font-medium' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Cari Lowongan') }}</span>
                    </a>
                    <a href="{{ route('applications.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('applications.index') ? 'text-primary bg-surface-container font-medium' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Lamaran Saya') }}</span>
                    </a>
                    <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('profile') ? 'text-primary bg-surface-container font-medium' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Profil Saya') }}</span>
                    </a>

                @elseif (auth()->user()->role === 'organization')
                    <div class="px-3 py-2 text-xs font-semibold text-outline-variant uppercase tracking-wider">
                        {{ __('Dashboard') }}
                    </div>
                    <a href="{{ route('organizer.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('organizer.dashboard') ? 'text-primary bg-surface-container font-medium' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Dashboard Penyelenggara') }}</span>
                    </a>

                    <div class="px-3 py-2 text-xs font-semibold text-outline-variant uppercase tracking-wider">
                        {{ __('Menu') }}
                    </div>
                    <a href="{{ route('organizer.events.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('organizer.events.create') ? 'text-primary bg-surface-container font-medium' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Buat Event') }}</span>
                    </a>
                    <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('profile') ? 'text-primary bg-surface-container font-medium' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <span class="font-medium text-sm">{{ __('Profil Organisasi') }}</span>
                    </a>

                @elseif (auth()->user()->role === 'admin')
                    <div class="px-3 py-2 text-xs font-semibold text-outline-variant uppercase tracking-wider">
                        {{ __('Dashboard') }}
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('admin.dashboard') ? 'text-primary bg-surface-container font-medium' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Dashboard Admin') }}</span>
                    </a>

                    <div class="px-3 py-2 text-xs font-semibold text-outline-variant uppercase tracking-wider">
                        {{ __('Menu') }}
                    </div>
                    <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('admin.users') ? 'text-primary bg-surface-container font-medium' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Manajemen Pengguna') }}</span>
                    </a>
                    <a href="{{ route('admin.skills') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('admin.skills') ? 'text-primary bg-surface-container font-medium' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}" wire:navigate>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V9a2 2 0 00-2-2h-1.172a2 2 0 01-1.414-.586l-.828-.828A2 2 0 0013.172 5H10.83a2 2 0 00-1.414.586l-.828.828A2 2 0 017.172 7H6a2 2 0 00-2 2v9a2 2 0 002 2z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Moderasi Keahlian') }}</span>
                    </a>
                    <a href="{{ route('admin.verifications') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('admin.verifications') ? 'text-primary bg-surface-container font-medium' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Verifikasi Ormawa') }}</span>
                    </a>
                @endif
            @endif
        </nav>

        @if (auth()->check())
            <div class="p-4 border-t border-surface-dim">
                <div class="flex items-center gap-3">
                    @if(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" alt="User avatar" class="w-9 h-9 rounded-full object-cover">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=2563eb&color=fff" alt="User avatar" class="w-9 h-9 rounded-full">
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-on-surface truncate">{{ auth()->user()->name }}</p>
                        <div class="flex items-center gap-1.5 text-xs">
                            @if (auth()->user()->role !== 'admin')
                                <a href="/profile" class="text-xs text-primary hover:text-primary font-medium truncate">{{ __('Lihat Profil') }}</a>
                                <span class="text-outline-variant">&bull;</span>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-error hover:text-on-error-container font-medium focus:outline-none">
                                    {{ __('Keluar') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </aside>

    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="bg-surface-container-lowest border-b border-surface-dim h-16 flex items-center px-6 md:px-8 flex-shrink-0 justify-between">
            <div class="flex items-center">
                <button x-on:click="sidebarOpen = true" class="text-on-surface-variant hover:text-on-surface focus:outline-none md:hidden mr-4">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-xl font-semibold text-on-surface">@yield('title', 'Dashboard')</h1>
            </div>
            <button class="text-outline-variant hover:text-on-surface-variant">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            </button>
        </header>

        <div class="flex-1 overflow-y-auto p-6 md:p-8">
            <div class="max-w-6xl mx-auto">
                @yield('content')
                @isset($slot)
                    {{ $slot }}
                @endisset
            </div>
        </div>
    </main>

    @livewireScripts
</body>
</html>
