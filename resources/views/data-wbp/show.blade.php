<x-app-layout :title="'Detail '.$wbp->nama">
    <x-page-header title="Detail WBP" :description="$wbp->nama.' — Reg. '.$wbp->no_register">
        <x-slot:actions>
            <a href="{{ route('wbp.index') }}" class="btn-secondary">
                <x-icon name="chevron-left" class="w-4 h-4" />
                Kembali
            </a>
            @can('update', $wbp)
                <a href="{{ route('wbp.edit', $wbp) }}" class="btn-primary">
                    <x-icon name="pencil" class="w-4 h-4" />
                    Edit
                </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card card-padding">
            <div class="flex items-center gap-4">
                <div class="size-16 rounded-xl bg-ink/5 flex items-center justify-center text-ink text-2xl font-semibold shrink-0">
                    {{ strtoupper(substr($wbp->nama, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <h2 class="truncate">{{ $wbp->nama }}</h2>
                    <p class="text-sm text-muted mt-0.5">Blok {{ $wbp->blok_kamar }}</p>
                    <span class="badge mt-2 {{ $wbp->status === App\Enums\WbpStatus::Aktif ? 'badge-green' : 'badge-gray' }}">
                        {{ $wbp->status->value }}
                    </span>
                </div>
            </div>

            <div class="mt-6 border-t border-line pt-5">
                <h3 class="text-sm font-semibold text-ink mb-4">Pemenuhan Hak</h3>
                <div class="flex items-end justify-between mb-2">
                    <span class="text-xs text-muted">{{ $totalCategories }} kategori layanan</span>
                    <span class="text-lg font-semibold text-ink">{{ $progress }}%</span>
                </div>
                <div class="h-2 rounded-full bg-ink/5 overflow-hidden">
                    <div class="h-full rounded-full {{ $progress >= 75 ? 'bg-teal-brand' : ($progress >= 40 ? 'bg-primary' : 'bg-orange-500') }}"
                         style="width: {{ $progress }}%"></div>
                </div>
            </div>

            <dl class="mt-6 border-t border-line pt-5 space-y-3">
                <div class="flex justify-between gap-4">
                    <dt class="text-sm text-muted">NIK</dt>
                    <dd class="text-sm text-ink font-semibold text-right">{{ $wbp->nik }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-sm text-muted">Nomor Register</dt>
                    <dd class="text-sm text-ink font-semibold text-right">{{ $wbp->no_register }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-sm text-muted">Jenis Kelamin</dt>
                    <dd class="text-sm text-ink font-semibold text-right">{{ $wbp->jenis_kelamin ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-sm text-muted">Agama</dt>
                    <dd class="text-sm text-ink font-semibold text-right">{{ $wbp->agama }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-sm text-muted">No. HP</dt>
                    <dd class="text-sm text-ink font-semibold text-right">{{ $wbp->no_hp ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-sm text-muted">Blok Kamar</dt>
                    <dd class="text-sm text-ink font-semibold text-right">{{ $wbp->blok_kamar }}</dd>
                </div>
            </dl>
        </div>

        <div class="card overflow-hidden xl:col-span-2">
            <div class="px-6 pt-5 pb-4 border-b border-line">
                <h2>Riwayat Monitoring</h2>
                <p class="text-sm text-muted mt-0.5">Catatan pemenuhan hak terbaru per layanan</p>
            </div>

            <div class="table-scroll">
                <table class="w-full min-w-[800px]">
                    <thead>
                        <tr class="border-b border-line bg-canvas-parchment/70">
                            <th class="table-th">Layanan</th>
                            <th class="table-th">Tanggal</th>
                            <th class="table-th">Waktu</th>
                            <th class="table-th">Status</th>
                            <th class="table-th">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($wbp->monitoringLogs->take(15) as $log)
                            <tr class="hover:bg-canvas-parchment/50 transition-colors">
                                <td class="table-td">
                                    <span class="text-ink font-semibold">{{ $log->category->nama_layanan }}</span>
                                    @if ($log->keterangan)
                                        <p class="text-xs text-muted mt-0.5 truncate max-w-[260px]">{{ $log->keterangan }}</p>
                                    @endif
                                </td>
                                <td class="table-td text-body">{{ $log->tanggal->translatedFormat('d M Y') }}</td>
                                <td class="table-td text-body">{{ $log->waktu_mulai ?? '—' }}</td>
                                <td class="table-td">
                                    @php
                                        $isKesehatan = $log->category?->slug === 'pemeriksaan-kesehatan';
                                        $statusLabel = $isKesehatan ? $log->status->labelKesehatan() : $log->status->value;
                                        $statusDone = $isKesehatan ? $statusLabel === 'Sudah' : $log->isTerpenuhi();
                                        $statusColor = $statusDone
                                            ? 'badge-green'
                                            : ($log->status === App\Enums\MonitoringStatus::Dibatalkan ? 'badge-gray' : 'badge-orange');
                                    @endphp
                                    <span class="badge {{ $statusColor }}">{{ $statusLabel }}</span>
                                </td>
                                <td class="table-td text-muted">{{ $log->user?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-muted">
                                    <p class="text-sm">Belum ada catatan monitoring untuk WBP ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden mt-6">
        <div class="px-6 pt-5 pb-4 border-b border-line">
            <h2>Riwayat Peminjaman Buku</h2>
        </div>

        <div class="table-scroll">
            <table class="w-full min-w-[520px]">
                <thead>
                    <tr class="border-b border-line bg-canvas-parchment/70">
                        <th class="table-th">Judul Buku</th>
                        <th class="table-th">Tanggal Pinjam</th>
                        <th class="table-th">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($wbp->borrowBooks as $borrow)
                        <tr class="hover:bg-canvas-parchment/50 transition-colors">
                            <td class="table-td text-ink font-semibold truncate max-w-[280px]">{{ $borrow->judul_buku }}</td>
                            <td class="table-td text-body">{{ $borrow->tanggal_pinjam->translatedFormat('d M Y') }}</td>
                            <td class="table-td">
                                <span class="badge {{ $borrow->status === App\Enums\BorrowStatus::Dikembalikan ? 'badge-green' : ($borrow->isOverdue() ? 'badge-red' : 'badge-blue') }}">
                                    {{ $borrow->status->value }}{{ $borrow->isOverdue() ? ' (terlambat)' : '' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-muted">
                                <p class="text-sm">Belum ada riwayat peminjaman buku.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
