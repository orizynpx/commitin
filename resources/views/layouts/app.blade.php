<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'COMMITIN')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts / Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased text-gray-900 bg-slate-50 flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
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

    <!-- Sidebar -->
    <aside class="fixed md:static inset-y-0 left-0 z-40 w-64 bg-white flex-shrink-0 h-full flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out border-r border-gray-200" 
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
        <!-- Logo -->
        <div class="h-16 flex items-center px-6 border-b border-gray-100 justify-between">
            <a href="/" class="text-2xl font-bold text-blue-700 flex items-center gap-2">
                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
                CommitIn
            </a>
            <!-- Mobile Close Button -->
            <button x-on:click="sidebarOpen = false" class="md:hidden text-gray-500 hover:text-gray-700 focus:outline-none">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            @if (auth()->check())
                @if (auth()->user()->role === 'student')
                    <!-- Student Navigation -->
                    <!-- Cari Lowongan -->
                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->is('explore') ? 'text-blue-700 bg-blue-50 font-medium' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Cari Lowongan') }}</span>
                    </a>
                    
                    <!-- Lamaran Saya -->
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('dashboard') ? 'text-blue-700 bg-blue-50 font-medium' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Lamaran Saya') }}</span>
                    </a>

                    <!-- Profil Saya -->
                    <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('profile') ? 'text-blue-700 bg-blue-50 font-medium' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Profil Saya') }}</span>
                    </a>

                @elseif (auth()->user()->role === 'organization')
                    <!-- Organization Navigation -->
                    <!-- Dashboard Event -->
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('dashboard') ? 'text-blue-700 bg-blue-50 font-medium' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Dashboard Event') }}</span>
                    </a>

                    <!-- Buat Event -->
                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors text-gray-600 hover:bg-blue-50 hover:text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Buat Event') }}</span>
                    </a>

                    <!-- Profil Organisasi -->
                    <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('profile') ? 'text-blue-700 bg-blue-50 font-medium' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <span class="font-medium text-sm">{{ __('Profil Organisasi') }}</span>
                    </a>

                @elseif (auth()->user()->role === 'admin')
                    <!-- Admin Navigation -->
                    <!-- Dashboard Admin -->
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('admin.dashboard') ? 'text-blue-700 bg-blue-50 font-medium' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Dashboard Admin') }}</span>
                    </a>

                    <!-- Moderasi Keahlian -->
                    <a href="{{ route('admin.skills') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('admin.skills') ? 'text-blue-700 bg-blue-50 font-medium' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}" wire:navigate>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V9a2 2 0 00-2-2h-1.172a2 2 0 01-1.414-.586l-.828-.828A2 2 0 0013.172 5H10.83a2 2 0 00-1.414.586l-.828.828A2 2 0 017.172 7H6a2 2 0 00-2 2v9a2 2 0 002 2z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Moderasi Keahlian') }}</span>
                    </a>

                    <!-- Verifikasi Ormawa -->
                    <a href="{{ route('admin.verifications') }}" class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors {{ request()->routeIs('admin.verifications') ? 'text-blue-700 bg-blue-50 font-medium' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        <span class="font-medium text-sm">{{ __('Verifikasi Ormawa') }}</span>
                    </a>
                @endif
            @endif
        </nav>

        <!-- User Profile Area at Bottom -->
        @if (auth()->check())
            <div class="p-4 border-t border-gray-100">
                <div class="flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=2563eb&color=fff" alt="User avatar" class="w-9 h-9 rounded-full">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                        <div class="flex items-center gap-1.5 text-xs">
                            @if (auth()->user()->role !== 'admin')
                                <a href="/profile" class="text-xs text-blue-500 hover:text-blue-700 font-medium truncate">{{ __('Lihat Profil') }}</a>
                                <span class="text-gray-300">&bull;</span>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium focus:outline-none">
                                    {{ __('Keluar') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 h-16 flex items-center px-6 md:px-8 flex-shrink-0 justify-between">
            <div class="flex items-center">
                <!-- Hamburger Menu Button (Mobile Only) -->
                <button x-on:click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 focus:outline-none md:hidden mr-4">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-xl font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
            </div>
            <!-- Notification icon placeholder -->
            <button class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            </button>
        </header>

        <!-- Scrollable Content -->
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
