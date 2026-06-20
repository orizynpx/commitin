<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-on-surface antialiased bg-background">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div>
                <a href="/" wire:navigate>
                    <x-application-logo class="h-12 w-auto" />
                </a>
            </div>

            <!-- Login Card with Corporate Modern styling -->
            <div class="w-full sm:max-w-md mt-8 px-8 py-8 bg-surface-container-lowest border border-surface-dim shadow-sm overflow-hidden rounded-lg relative">
                <!-- Yellow Accent Line representing the secondary color from the palette -->
                <div class="absolute top-0 left-0 w-full h-1 bg-secondary-container"></div>
                
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
