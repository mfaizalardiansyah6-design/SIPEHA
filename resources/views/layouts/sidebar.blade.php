<aside class="fixed inset-y-0 left-0 z-40 w-60 bg-sidebar flex flex-col transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0"
       :class="$store.mobileNav.open ? 'translate-x-0' : '-translate-x-full'"
       x-data="sidebar">
    <div class="flex items-center gap-3 px-5 pt-5 pb-4">
        <div class="size-9 rounded-lg bg-primary flex items-center justify-center text-white text-sm font-semibold shrink-0 overflow-hidden">
            @if (! empty($instansiLogoUrl))
                <img src="{{ $instansiLogoUrl }}" alt="{{ $instansi['nama'] }}" class="size-9 object-cover">
            @else
                {{ strtoupper(substr($instansi['nama'] ?? 'H', 0, 1)) }}
            @endif
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-white font-semibold text-sm leading-tight truncate">{{ $instansi['nama'] ?? 'SIMHAK WBP' }}</p>
            <p class="text-xs text-white/45 leading-tight">{{ $instansi['kode'] ?: 'Sistem Monitoring Hak' }}</p>
        </div>
        <button type="button" class="lg:hidden inline-flex items-center justify-center size-9 rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition-colors" @click="$store.mobileNav.close()" aria-label="Tutup menu">
            <x-icon name="x" class="w-5 h-5" />
        </button>
    </div>

    <nav class="sidebar-scroll flex-1 overflow-y-auto px-3 pb-4 space-y-0.5" x-data="{}"
         @click="if ($event.target.closest('a')) $store.mobileNav.close()">
        @php
            $routeName = request()->route()?->getName();
            $isAdmin = auth()->user()->isAdmin();
            $monitoringActive = str_starts_with($routeName ?? '', 'monitoring.');
        @endphp

        <a href="{{ route('dashboard') }}"
           class="nav-item {{ $routeName === 'dashboard' ? 'nav-active' : '' }}">
            @if ($routeName === 'dashboard')
                <span class="absolute left-0 inset-y-0 w-[3px] bg-primary"></span>
            @endif
            <x-icon name="home" class="w-5 h-5 shrink-0" />
            <span>Dashboard</span>
        </a>

        <a href="{{ route('wbp.index') }}"
           class="nav-item {{ str_starts_with($routeName ?? '', 'wbp.') ? 'nav-active' : '' }}">
            @if (str_starts_with($routeName ?? '', 'wbp.'))
                <span class="absolute left-0 inset-y-0 w-[3px] bg-primary"></span>
            @endif
            <x-icon name="user" class="w-5 h-5 shrink-0" />
            <span>Data WBP</span>
        </a>

        <p class="nav-section-label">Monitoring</p>

        <div data-menu="monitoring" data-open="{{ $monitoringActive ? 'true' : 'false' }}">
            <button type="button" @click="toggle('monitoring')"
                    class="nav-item w-full {{ $monitoringActive ? 'nav-active' : '' }}">
                @if ($monitoringActive)
                    <span class="absolute left-0 inset-y-0 w-[3px] bg-primary"></span>
                @endif
                <x-icon name="list-bullet" class="w-5 h-5 shrink-0" />
                <span class="flex-1 text-left">Monitoring Hak</span>
                <x-icon :name="$monitoringActive ? 'chevron-down' : 'chevron-right'" class="w-4 h-4 shrink-0 transition-transform" />
            </button>

            <div x-show="isOpen('monitoring')" class="mt-0.5 space-y-0.5">
                @php $isVideo = $routeName === 'monitoring.video-call'; @endphp
                <a href="{{ route('monitoring.video-call') }}"
                   class="nav-sub {{ $isVideo ? 'nav-active' : '' }}">
                    @if ($isVideo)
                        <span class="absolute left-0 inset-y-0 w-[3px] bg-primary"></span>
                    @endif
                    <x-icon name="video-camera" class="w-5 h-5 shrink-0" />
                    <span>Video Call</span>
                </a>

                @php
                    $isPerawatan = $routeName === 'monitoring.perawatan';
                    $tab = request('tab', 'potong-rambut');
                    $perawatanItems = [
                        'potong-rambut' => 'Potong Rambut',
                        'potong-kuku' => 'Potong Kuku',
                    ];
                @endphp
                <div data-menu="perawatan" data-open="{{ $isPerawatan ? 'true' : 'false' }}">
                    <button type="button" @click="toggle('perawatan')"
                            class="nav-sub w-full {{ $isPerawatan ? 'nav-active' : '' }}">
                        @if ($isPerawatan)
                            <span class="absolute left-0 inset-y-0 w-[3px] bg-primary"></span>
                        @endif
                        <x-icon name="scissors" class="w-5 h-5 shrink-0" />
                        <span class="flex-1 text-left">Perawatan Diri</span>
                        <x-icon :name="$isPerawatan ? 'chevron-down' : 'chevron-right'" class="w-4 h-4 shrink-0" />
                    </button>

                    <div x-show="isOpen('perawatan')" class="space-y-0.5">
                        @foreach ($perawatanItems as $slug => $label)
                            @php $itemActive = $isPerawatan && $tab === $slug; @endphp
                            <a href="{{ route('monitoring.perawatan', ['tab' => $slug]) }}"
                               class="nav-subsub {{ $itemActive ? 'nav-active' : '' }}">
                                @if ($itemActive)
                                    <span class="absolute left-0 inset-y-0 w-[3px] bg-primary"></span>
                                @endif
                                <span class="size-1.5 rounded-full bg-current"></span>
                                <span>{{ $label }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                @php $isKesehatan = $routeName === 'monitoring.pemeriksaan-kesehatan'; @endphp
                <a href="{{ route('monitoring.pemeriksaan-kesehatan') }}"
                   class="nav-sub {{ $isKesehatan ? 'nav-active' : '' }}">
                    @if ($isKesehatan)
                        <span class="absolute left-0 inset-y-0 w-[3px] bg-primary"></span>
                    @endif
                    <x-icon name="activity" class="w-5 h-5 shrink-0" />
                    <span>Pemeriksaan Kesehatan</span>
                </a>

                @php $isBuku = str_starts_with($routeName ?? '', 'monitoring.buku'); @endphp
                <a href="{{ route('monitoring.buku') }}"
                   class="nav-sub {{ $isBuku ? 'nav-active' : '' }}">
                    @if ($isBuku)
                        <span class="absolute left-0 inset-y-0 w-[3px] bg-primary"></span>
                    @endif
                    <x-icon name="book-open" class="w-5 h-5 shrink-0" />
                    <span>Peminjaman Buku</span>
                </a>

                @php $isLaundry = $routeName === 'monitoring.laundry'; @endphp
                <a href="{{ route('monitoring.laundry') }}"
                   class="nav-sub {{ $isLaundry ? 'nav-active' : '' }}">
                    @if ($isLaundry)
                        <span class="absolute left-0 inset-y-0 w-[3px] bg-primary"></span>
                    @endif
                    <x-icon name="shirt" class="w-5 h-5 shrink-0" />
                    <span>Layanan Laundry</span>
                </a>
            </div>
        </div>

        <a href="{{ route('jadwal') }}"
           class="nav-item {{ $routeName === 'jadwal' ? 'nav-active' : '' }}">
            @if ($routeName === 'jadwal')
                <span class="absolute left-0 inset-y-0 w-[3px] bg-primary"></span>
            @endif
            <x-icon name="calendar" class="w-5 h-5 shrink-0" />
            <span>Jadwal Pelayanan</span>
        </a>

        <a href="{{ route('laporan.index') }}"
           class="nav-item {{ str_starts_with($routeName ?? '', 'laporan.') ? 'nav-active' : '' }}">
            @if (str_starts_with($routeName ?? '', 'laporan.'))
                <span class="absolute left-0 inset-y-0 w-[3px] bg-primary"></span>
            @endif
            <x-icon name="chart-bar" class="w-5 h-5 shrink-0" />
            <span>Laporan</span>
        </a>

        @if ($isAdmin)
            <p class="nav-section-label">Administrasi</p>

            <a href="{{ route('petugas.index') }}"
               class="nav-item {{ str_starts_with($routeName ?? '', 'petugas.') ? 'nav-active' : '' }}">
                @if (str_starts_with($routeName ?? '', 'petugas.'))
                    <span class="absolute left-0 inset-y-0 w-[3px] bg-primary"></span>
                @endif
                <x-icon name="users" class="w-5 h-5 shrink-0" />
                <span>Data Petugas</span>
            </a>

            <a href="{{ route('pengaturan.index') }}"
               class="nav-item {{ str_starts_with($routeName ?? '', 'pengaturan.') ? 'nav-active' : '' }}">
                @if (str_starts_with($routeName ?? '', 'pengaturan.'))
                    <span class="absolute left-0 inset-y-0 w-[3px] bg-primary"></span>
                @endif
                <x-icon name="settings" class="w-5 h-5 shrink-0" />
                <span>Pengaturan</span>
            </a>

            <a href="{{ route('audit-logs') }}"
               class="nav-item {{ $routeName === 'audit-logs' ? 'nav-active' : '' }}">
                @if ($routeName === 'audit-logs')
                    <span class="absolute left-0 inset-y-0 w-[3px] bg-primary"></span>
                @endif
                <x-icon name="shield-check" class="w-5 h-5 shrink-0" />
                <span>Audit Log</span>
            </a>
        @endif
    </nav>

    <div class="border-t border-white/10 px-3 py-3 flex items-center gap-3">
        <div class="flex items-center gap-3 min-w-0 flex-1">
            <div class="size-9 rounded-full bg-white/10 flex items-center justify-center text-white text-sm font-semibold shrink-0 overflow-hidden">
                @if (auth()->user()->profilePhotoUrl)
                    <img src="{{ auth()->user()->profilePhotoUrl }}" alt="{{ auth()->user()->name }}" class="size-9 object-cover">
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>
            <div class="min-w-0">
                <p class="text-white text-sm font-semibold leading-tight truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-white/45 leading-tight truncate">{{ auth()->user()->role->label() }}</p>
            </div>
        </div>
    </div>
</aside>
