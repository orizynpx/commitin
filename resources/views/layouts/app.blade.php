<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'COMMITLN')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts / Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased text-gray-900 bg-slate-50 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex-shrink-0 h-full flex flex-col">
        <!-- Logo -->
        <div class="h-16 flex items-center px-6 border-b border-gray-100">
            <a href="/" class="text-2xl font-bold text-blue-700 flex items-center gap-2">
                <!-- Simple SVG Logo Placeholder -->
                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
                CommitIn
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            
            <!-- Beranda -->
            <a href="/" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->is('/') ? 'text-blue-700 bg-blue-50 font-medium' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="font-medium text-sm">Beranda</span>
            </a>

            <!-- Dashboard Dropdown (AlpineJS for state) -->
            <div x-data="{ open: {{ request()->is('pelamar') || request()->is('dashboard') ? 'true' : 'false' }} }" class="space-y-1">
                <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-md transition-colors {{ request()->is('pelamar') || request()->is('dashboard') ? 'text-blue-700 bg-blue-50' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span class="font-medium text-sm">Dashboard</span>
                    </div>
                    <!-- Chevron -->
                    <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <!-- Dropdown Items -->
                <div x-show="open" x-collapse class="pl-11 pr-3 space-y-1">
                    <a href="/pelamar" class="block px-3 py-2 text-sm rounded-md {{ request()->is('pelamar') ? 'font-medium text-blue-600 bg-blue-50/50' : 'text-gray-500 hover:text-blue-600 hover:bg-gray-50' }}">Pelamar</a>
                    <a href="/dashboard" class="block px-3 py-2 text-sm rounded-md {{ request()->is('dashboard') ? 'font-medium text-blue-600 bg-blue-50/50' : 'text-gray-500 hover:text-blue-600 hover:bg-gray-50' }}">Penyelenggara</a>
                </div>
            </div>

            <a href="/explore" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->is('explore') ? 'text-blue-700 bg-blue-50 font-medium' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <span class="font-medium text-sm">Cari Lowongan</span>
            </a>
            
            @if(request()->is('dashboard'))
            <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <span class="font-medium text-sm">Manage Vacancies</span>
            </a>
            @endif

            @if (auth()->check() && auth()->user()->role === 'admin')
                <div class="pt-4 pb-2 px-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Menu Admin') }}</p>
                </div>
                <a href="{{ route('admin.skills') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('admin.skills') ? 'text-blue-700 bg-blue-50 font-medium' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}" wire:navigate>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V9a2 2 0 00-2-2h-1.172a2 2 0 01-1.414-.586l-.828-.828A2 2 0 0013.172 5H10.83a2 2 0 00-1.414.586l-.828.828A2 2 0 017.172 7H6a2 2 0 00-2 2v9a2 2 0 002 2z"></path></svg>
                    <span class="font-medium text-sm">Moderasi Keahlian</span>
                </a>
            @endif
            
        </nav>

        <!-- User Profile Area at Bottom -->
        <div class="p-4 border-t border-gray-100">
            <div class="flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=2563eb&color=fff" alt="User avatar" class="w-9 h-9 rounded-full">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    <div class="flex items-center gap-1.5 text-xs">
                        <a href="/profile" class="text-blue-500 hover:text-blue-700 font-medium truncate">{{ __('Lihat Profil') }}</a>
                        <span class="text-gray-300">&bull;</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium">
                                {{ __('Keluar') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        
        <!-- Header area for the current page (optional, but good for context) -->
        <header class="bg-white border-b border-gray-200 h-16 flex items-center px-8 flex-shrink-0 justify-between">
            <h1 class="text-xl font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
            <!-- Notification icon placeholder -->
            <button class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            </button>
        </header>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-8">
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
