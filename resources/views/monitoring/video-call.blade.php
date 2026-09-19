<x-app-layout title="Monitoring Video Call">
    <x-page-header title="Video Call" description="Jadwal dan riwayat sesi video call dengan keluarga">
        <x-slot:actions>
            <a href="{{ route('monitoring.video-call.export', array_merge(request()->query(), ['tanggal' => $tanggal])) }}" class="btn-secondary">
                <x-icon name="download" class="w-4 h-4" />
                Export Excel
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <x-stat-card
            icon="users"
            tone="blue"
            label="Total WBP Aktif"
            :value="number_format($totalWbp)"
        />

        <x-stat-card
            icon="check-circle"
            tone="teal"
            :label="'Selesai ' . \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y')"
            :value="number_format($selesai)"
            value-class="text-teal-brand"
        />

        <x-stat-card
            icon="clock"
            tone="orange"
            label="Belum Tercatat"
            :value="number_format($belum)"
            value-class="text-orange-500"
        />
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3 mt-6">
        <div class="card card-padding xl:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h2>Jadwal Sesi Hari Ini</h2>
                    <p class="text-sm text-muted mt-0.5">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</p>
                </div>

                <form method="GET" action="{{ route('monitoring.video-call') }}" class="flex flex-wrap items-center gap-2">
                    <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-input w-40 sm:!w-44" onchange="this.form.submit()">
                    <select name="blok" class="form-input w-32 sm:!w-36" onchange="this.form.submit()">
                        <option value="">Semua Blok</option>
                        @foreach ($blokList as $option)
                            <option value="{{ $option }}" @selected($blok === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="table-scroll">
                <table class="w-full min-w-[800px]">
                    <thead>
                        <tr class="border-b border-line bg-canvas-parchment/70">
                            <th class="table-th">WBP</th>
                            <th class="table-th">Waktu</th>
                            <th class="table-th">Status</th>
                            <th class="table-th">Keterangan</th>
                            <th class="table-th">Petugas</th>
                            <th class="table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-canvas-parchment/50 transition-colors">
                                <td class="table-td">
                                    <p class="font-semibold text-ink truncate max-w-[220px]">{{ $log->wbp?->nama ?? '—' }}</p>
                                    <p class="text-xs text-muted">Blok {{ $log->wbp?->blok_kamar ?? '—' }}</p>
                                </td>
                                <td class="table-td text-body">{{ $log->waktu_mulai ?? '—' }}</td>
                                <td class="table-td">
                                    <span class="badge {{ $log->isTerpenuhi() ? 'badge-green' : 'badge-orange' }}">{{ $log->status->value }}</span>
                                </td>
                                <td class="table-td text-body max-w-xs truncate">{{ $log->keterangan ?? '—' }}</td>
                                <td class="table-td text-muted">{{ $log->user?->name ?? '—' }}</td>
                                <td class="table-td">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form method="POST" action="{{ route('monitoring.update-status', $log) }}" class="flex items-center gap-1.5">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="form-input !w-28 !py-1.5 text-xs" onchange="this.form.submit()">
                                                @foreach ($log->category ? App\Enums\MonitoringStatus::optionsFor($log->category->slug) : ['Selesai', 'Belum'] as $option)
                                                    <option value="{{ $option }}" @selected($log->status->value === $option)>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                        <form method="POST" action="{{ route('monitoring.destroy', $log) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-muted hover:text-red-600 p-1 transition-colors" title="Hapus">
                                                <x-icon name="trash" class="w-4 h-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-muted">
                                    <x-icon name="video-camera" class="mx-auto w-10 h-10 mb-2 opacity-50" />
                                    <p class="text-sm">Belum ada sesi video call pada tanggal ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-line">
                {{ $logs->links() }}
            </div>
        </div>

        <div class="card card-padding h-fit">
            <h2 class="mb-4">Catat Sesi Baru</h2>

            <form method="POST" action="{{ route('monitoring.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="category_id" value="{{ $category->id }}">
                <input type="hidden" name="status" value="Selesai">

                <div>
                    <x-input-label for="wbp_id" value="WBP" />
                    <select id="wbp_id" name="wbp_id" class="form-input" required>
                        <option value="">Pilih WBP</option>
                        @foreach ($wbps as $wbp)
                            <option value="{{ $wbp->id }}">{{ $wbp->nama }} — Blok {{ $wbp->blok_kamar }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('wbp_id')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="tanggal" value="Tanggal" />
                        <input type="date" id="tanggal" name="tanggal" value="{{ $tanggal }}" class="form-input" required>
                    </div>
                    <div>
                        <x-input-label for="waktu_mulai" value="Waktu Mulai" />
                        <input type="time" id="waktu_mulai" name="waktu_mulai" class="form-input">
                    </div>
                </div>

                <div>
                    <x-input-label for="keterangan" value="Keterangan" />
                    <textarea id="keterangan" name="keterangan" rows="3" class="form-input" placeholder="contoh: video call dengan keluarga via WhatsApp"></textarea>
                </div>

                <button type="submit" class="btn-primary w-full">
                    <x-icon name="plus" class="w-4 h-4" />
                    Simpan Sesi
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
