<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'IT Worklog') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-bp.svg') }}">
        <link rel="alternate icon" href="{{ asset('images/logo-bp.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" class="app-shell min-h-screen bg-slate-50 text-slate-900">
            <div
                x-show="sidebarOpen"
                x-cloak
                class="fixed inset-0 z-40 lg:hidden"
                role="dialog"
                aria-modal="true"
                aria-label="Menu navigasi"
            >
                <div class="absolute inset-0 bg-slate-900/50" @click="sidebarOpen = false"></div>

                <aside class="app-sidebar absolute inset-y-0 left-0 flex w-72 max-w-[85vw] flex-col border-r border-slate-800 shadow-xl">
                    <button
                        type="button"
                        class="absolute right-3 top-4 z-10 rounded-lg p-2 text-slate-400 hover:bg-slate-800 hover:text-white"
                        @click="sidebarOpen = false"
                        aria-label="Tutup menu"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>

                    @include('layouts.sidebar')
                </aside>
            </div>

            <aside class="app-sidebar-frame hidden border-r border-slate-800 lg:fixed lg:inset-y-0 lg:z-30 lg:flex lg:w-64 lg:flex-col print:hidden">
                @include('layouts.sidebar')
            </aside>

            <div class="flex min-h-screen flex-col lg:pl-64">
                <nav class="app-topbar sticky top-0 z-30 border-b print:hidden">
                    <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                        <button
                            type="button"
                            class="app-icon-button lg:hidden"
                            @click="sidebarOpen = true"
                            aria-label="Buka menu"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-slate-900">IT Worklog — PT Borneo Prima</p>
                            <p class="truncate text-xs text-slate-500">Aktivitas harian dan progres tim IT</p>
                        </div>

                        <span class="hidden text-xs font-semibold text-slate-500 sm:inline">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </span>

                        <div x-data="{ open: false }" class="relative">
                            <button
                                type="button"
                                class="app-user-trigger"
                                @click="open = ! open"
                                :aria-expanded="open"
                                aria-haspopup="true"
                            >
                                <span class="max-w-[10rem] truncate">{{ auth()->user()->name }}</span>
                                <svg class="ms-2 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                </svg>
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                @click.outside="open = false"
                                class="ui-card absolute right-0 z-50 mt-2 w-52 overflow-hidden py-1"
                            >
                                <div class="border-b border-slate-100 px-4 py-3">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ auth()->user()->name }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                                </div>

                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                                    Profil
                                </a>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm font-semibold text-rose-600 hover:bg-rose-50">
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </nav>

                @isset($header)
                    <header class="app-page-header border-b print:hidden">
                        <div class="px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1">
                    <div class="px-4 py-6 sm:px-6 lg:px-8">
                        @if (session('status'))
                            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                                <p class="font-bold">Ada data yang perlu diperiksa.</p>
                                <ul class="mt-1 list-disc ps-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                </main>

                <footer class="app-footer border-t print:hidden">
                    <div class="flex flex-col gap-1 px-4 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                        <p>&copy; {{ date('Y') }} PT Borneo Prima — IT Worklog</p>
                        <p>Versi 1.1 MVP · Bahasa Indonesia</p>
                    </div>
                </footer>
            </div>
        </div>
    </body>
</html>
