<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="{{ $appearance['theme_color'] ?? 'blue' }}"
      data-mode="{{ $appearance['mode'] ?? 'light' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' — '.config('app.name') : config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @include('layouts.sidebar')

        <div class="fixed inset-0 z-30 bg-ink/40 backdrop-blur-sm transition-opacity lg:hidden"
             :class="$store.mobileNav.open ? 'opacity-100' : 'opacity-0 pointer-events-none'"
             x-data x-cloak @click="$store.mobileNav.close()"></div>

        <div class="lg:pl-60 min-h-screen flex flex-col">
            <header class="sticky top-0 z-20 h-16 bg-surface/80 backdrop-blur-md border-b border-line flex items-center justify-between gap-3 px-4 sm:px-6" x-data>
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" class="lg:hidden inline-flex items-center justify-center size-10 -ml-1 rounded-lg text-ink hover:bg-ink/5 transition-colors"
                            @click="$store.mobileNav.toggle()" aria-label="Buka menu">
                        <x-icon name="menu" class="w-6 h-6" />
                    </button>
                    <div class="min-w-0">
                        <p class="text-xs text-muted tracking-wide truncate">{{ now()->translatedFormat('l, d F Y') }}</p>
                        <h1 class="text-base font-semibold text-ink tracking-tight leading-tight truncate">{{ isset($title) ? $title : ($instansi['nama'] ?? 'SIMHAK WBP') }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <span class="hidden md:inline-flex items-center gap-2 text-sm tabular-nums text-body" x-data="clock" title="Waktu sekarang">
                        <x-icon name="clock" class="w-4 h-4 text-muted" />
                        <span x-text="time">--:--:--</span>
                    </span>

                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="relative inline-flex items-center justify-center size-10 -ml-1 rounded-lg text-ink hover:bg-ink/5 transition-colors" aria-label="Notifikasi">
                            <x-icon name="bell" class="w-5 h-5" />
                            <span class="absolute top-2 right-2 size-2 rounded-full bg-red-500 ring-2 ring-surface"></span>
                        </button>

                        <div x-show="open" x-transition x-cloak
                             class="absolute right-0 mt-2 w-72 rounded-xl bg-surface border border-line shadow-lg overflow-hidden">
                            <div class="px-4 py-3 border-b border-line">
                                <p class="text-sm font-semibold text-ink">Notifikasi</p>
                            </div>
                            <div class="px-4 py-6 text-center">
                                <div class="mx-auto size-10 rounded-full bg-ink/5 flex items-center justify-center text-muted mb-2">
                                    <x-icon name="check" class="w-5 h-5" />
                                </div>
                                <p class="text-sm text-muted">Tidak ada notifikasi baru.</p>
                            </div>
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('audit-logs') }}" class="block px-4 py-3 text-sm text-primary hover:bg-ink/5 border-t border-line transition-colors">
                                    Lihat Audit Log
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="flex items-center gap-3 group" title="Menu akun">
                            <span class="text-sm text-body hidden sm:block truncate max-w-40 group-hover:text-primary transition-colors">{{ auth()->user()->name }}</span>
                            <span class="inline-flex items-center justify-center size-9 rounded-full bg-ink/5 text-ink font-semibold text-sm shrink-0 overflow-hidden">
                                @if (auth()->user()->profilePhotoUrl)
                                    <img src="{{ auth()->user()->profilePhotoUrl }}" alt="{{ auth()->user()->name }}" class="size-9 object-cover">
                                @else
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                @endif
                            </span>
                            <x-icon name="chevron-down" class="w-4 h-4 text-muted shrink-0" />
                        </button>

                        <div x-show="open" x-transition x-cloak
                             class="absolute right-0 mt-2 w-60 rounded-xl bg-surface border border-line shadow-lg overflow-hidden">
                            <div class="px-4 py-3 border-b border-line">
                                <p class="text-sm font-semibold text-ink truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-muted truncate">{{ auth()->user()->role->label() }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('pengaturan-saya.edit') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-body hover:bg-ink/5 transition-colors">
                                    <x-icon name="settings" class="w-4 h-4 text-muted" />
                                    Pengaturan Saya
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-500/5 transition-colors text-left">
                                        <x-icon name="logout" class="w-4 h-4" />
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @if (session('success'))
                    <div class="mb-6 flex items-center gap-2 rounded-xl bg-teal-brand/10 text-teal-dark px-4 py-3 text-sm">
                        <x-icon name="check-circle" class="w-5 h-5 shrink-0" />
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 flex items-center gap-2 rounded-xl bg-red-500/10 text-red-600 px-4 py-3 text-sm">
                        <x-icon name="x-circle" class="w-5 h-5 shrink-0" />
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl bg-red-500/10 text-red-600 px-4 py-3 text-sm">
                        <div class="flex items-start gap-2">
                            <x-icon name="exclamation-triangle" class="w-5 h-5 shrink-0" />
                            <ul class="space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </main>

            <footer class="px-4 sm:px-6 py-5 text-xs text-muted border-t border-line bg-canvas-parchment">
                &copy; {{ date('Y') }} {{ $instansi['nama'] ?? 'SIMHAK WBP' }} &mdash; Sistem Monitoring Pemenuhan Hak Warga Binaan
            </footer>
        </div>
    </body>
</html>
