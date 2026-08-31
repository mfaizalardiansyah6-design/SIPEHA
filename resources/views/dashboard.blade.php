<x-app-layout title="Dashboard">
    @php
        $chartLabels = $stats['per_category']->pluck('nama');
        $chartValues = $stats['per_category']->pluck('persen');
        $blokLabels = $stats['per_blok']->keys();
        $blokValues = $stats['per_blok']->values();
        $blokColors = ['#0066cc', '#2997ff', '#1d1d1f', '#7a7a7a', '#c8c8cc', '#4a7fb0', '#2a2a2c', '#b6cce3'];

        $barChartData = [
            'chart' => ['type' => 'bar', 'height' => 320, 'fontFamily' => 'Inter', 'toolbar' => ['show' => false]],
            'plotOptions' => ['bar' => ['horizontal' => true, 'barHeight' => '45%', 'borderRadius' => 4]],
            'series' => [['name' => 'Pemenuhan', 'data' => $chartValues]],
            'xaxis' => ['categories' => $chartLabels, 'max' => 100],
            'colors' => ['#0066cc'],
            'dataLabels' => ['enabled' => false],
            'grid' => ['borderColor' => '#e0e0e0'],
        ];

        $donutChartData = [
            'chart' => ['type' => 'donut', 'height' => 320, 'fontFamily' => 'Inter'],
            'labels' => $blokLabels,
            'series' => $blokValues,
            'colors' => $blokColors,
            'legend' => ['position' => 'bottom'],
            'stroke' => ['width' => 0],
            'dataLabels' => ['enabled' => false],
            'plotOptions' => ['pie' => ['donut' => ['size' => '72%']]],
        ];
    @endphp

    <script type="application/json" id="bar-chart-data">{!! Js::from($barChartData) !!}</script>

    <script type="application/json" id="donut-chart-data">{!! Js::from($donutChartData) !!}</script>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-5">
        <x-stat-card
            icon="user"
            tone="blue"
            label="WBP Aktif"
            :value="number_format($stats['total_wbp'])"
            :subtitle="number_format($stats['total_wbp_all']) . ' total · ' . number_format($stats['nonaktif_wbp']) . ' non-aktif'"
        />

        <x-stat-card
            icon="users"
            label="Petugas"
            :value="number_format($stats['total_petugas'])"
            :subtitle="number_format($stats['petugas_admin']) . ' admin · ' . number_format($stats['petugas_user']) . ' petugas'"
        />

        <x-stat-card
            icon="check-circle"
            tone="teal"
            label="Hak Terpenuhi Hari Ini"
            :value="number_format($stats['terpenuhi_hari_ini'])"
            subtitle="catatan layanan terpenuhi hari ini"
        />

        <x-stat-card
            icon="clock"
            tone="orange"
            label="Hak Belum Terpenuhi Hari Ini"
            :value="number_format($stats['belum_hari_ini'])"
            subtitle="catatan layanan belum terpenuhi hari ini"
        />

        <x-stat-card
            icon="shield-check"
            tone="blue"
            label="Total Layanan Selesai"
            :value="number_format($stats['terpenuhi_total'])"
            subtitle="akumulasi seluruh catatan selesai"
        />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-5">
        <div class="card overflow-hidden xl:col-span-3">
            <div class="px-6 pt-5 pb-4 border-b border-line">
                <h2>Pemenuhan Hak per Layanan</h2>
                <p class="text-sm text-muted mt-0.5">Persentase WBP dengan hak terpenuhi (30 hari terakhir)</p>
            </div>
            <div class="p-6" x-data="chart('bar-chart-data')">
                <div x-ref="container"></div>
            </div>
        </div>

        <div class="card overflow-hidden xl:col-span-2">
            <div class="px-6 pt-5 pb-4 border-b border-line">
                <h2>Sebaran WBP per Blok</h2>
                <p class="text-sm text-muted mt-0.5">Distribusi WBP aktif berdasarkan blok hunian</p>
            </div>
            <div class="p-6" x-data="chart('donut-chart-data')">
                <div x-ref="container"></div>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card xl:col-span-2 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-line">
                <h2>Notifikasi Hak Belum Terpenuhi</h2>
                <p class="text-sm text-muted mt-0.5">WBP dengan layanan terhambat lebih dari 7 hari</p>
            </div>

            @if ($stats['notifications']->isEmpty())
                <div class="px-6 py-10 text-center">
                    <div class="mx-auto size-12 rounded-full bg-teal-brand/10 flex items-center justify-center text-teal-brand mb-3">
                        <x-icon name="check" class="w-6 h-6" />
                    </div>
                    <p class="text-sm text-muted">Tidak ada keterlambatan. Seluruh hak WBP terpantau.</p>
                </div>
            @else
                <div class="table-scroll">
                    <table class="w-full min-w-[640px]">
                        <thead>
                            <tr class="border-b border-line bg-canvas-parchment/70">
                                <th class="table-th">WBP</th>
                                <th class="table-th">Kategori Terhambat</th>
                                <th class="table-th">Jumlah</th>
                                <th class="table-th">Keterlambatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($stats['notifications'] as $item)
                                <tr class="hover:bg-canvas-parchment/50 transition-colors">
                                    <td class="table-td">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="size-9 rounded-full bg-ink/5 flex items-center justify-center text-ink text-xs font-semibold shrink-0">
                                                {{ strtoupper(substr($item['nama'], 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-ink truncate max-w-[200px]">{{ $item['nama'] }}</p>
                                                <p class="text-xs text-muted">Blok {{ $item['blok_kamar'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="table-td">
                                        <span class="text-body">{{ $item['kategori'] }}</span>
                                    </td>
                                    <td class="table-td">
                                        <span class="badge {{ $item['jumlah'] > 2 ? 'badge-red' : 'badge-orange' }}">{{ $item['jumlah'] }} kategori</span>
                                    </td>
                                    <td class="table-td">
                                        <span class="badge {{ $item['hari'] > 14 ? 'badge-red' : 'badge-orange' }}">{{ $item['hari'] }} hari</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="card overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-line">
                <h2>Hari Ini</h2>
                <p class="text-sm text-muted mt-0.5">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>

            <div class="p-6">
                <a href="{{ route('jadwal') }}" class="flex items-center gap-3 rounded-lg bg-ink/[0.03] border border-divider-soft px-4 py-3 hover:bg-ink/5 transition-colors">
                    <div class="size-10 rounded-lg bg-ink/5 flex items-center justify-center text-ink shrink-0">
                        <x-icon name="calendar" class="w-5 h-5" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-ink">Jadwal Pelayanan</p>
                        <p class="text-xs text-muted">Lihat agenda dan kalender sesi</p>
                    </div>
                    <x-icon name="chevron-right" class="w-5 h-5 text-muted" />
                </a>

                <a href="{{ route('laporan.index') }}" class="mt-3 flex items-center gap-3 rounded-lg bg-ink/[0.03] border border-divider-soft px-4 py-3 hover:bg-ink/5 transition-colors">
                    <div class="size-10 rounded-lg bg-ink/5 flex items-center justify-center text-ink shrink-0">
                        <x-icon name="chart-bar" class="w-5 h-5" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-ink">Laporan</p>
                        <p class="text-xs text-muted">Unduh laporan Excel / PDF</p>
                    </div>
                    <x-icon name="chevron-right" class="w-5 h-5 text-muted" />
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
