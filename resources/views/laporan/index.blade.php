<x-app-layout title="Laporan">
    <x-page-header title="Laporan Monitoring" description="Rekapitulasi pemenuhan hak WBP per periode">
        <x-slot:actions>
            <a href="{{ route('laporan.excel', request()->query()) }}" class="btn-secondary">
                <x-icon name="download" class="w-4 h-4" />
                Export Excel
            </a>
            <a href="{{ route('laporan.pdf', request()->query()) }}" class="btn-danger">
                <x-icon name="printer" class="w-4 h-4" />
                Export PDF
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-6">
        <form method="GET" action="{{ route('laporan.index') }}" class="p-4 grid grid-cols-1 gap-3 sm:grid-cols-4">
            <div>
                <x-input-label for="dari" value="Dari Tanggal" />
                <input type="date" id="dari" name="dari" value="{{ $start }}" class="form-input">
            </div>
            <div>
                <x-input-label for="sampai" value="Sampai Tanggal" />
                <input type="date" id="sampai" name="sampai" value="{{ $end }}" class="form-input">
            </div>
            <div>
                <x-input-label for="kategori" value="Kategori Layanan" />
                <select id="kategori" name="kategori" class="form-input">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $kategori === (string) $category->id)>{{ $category->nama_layanan }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-secondary w-full">
                    <x-icon name="filter" class="w-4 h-4" />
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-6">
        <x-stat-card
            icon="document"
            tone="blue"
            label="Total Pencatatan"
            :value="number_format($summary['total'])"
        />

        <x-stat-card
            icon="check-circle"
            tone="teal"
            label="Hak Terpenuhi"
            :value="number_format($summary['terpenuhi'])"
            value-class="text-teal-brand"
        />

        <x-stat-card
            icon="clock"
            tone="orange"
            label="Hak Belum Terpenuhi"
            :value="number_format($summary['belum'])"
            value-class="text-orange-500"
        />
    </div>

    <div class="card overflow-hidden">
        <div class="px-6 pt-5 pb-4 border-b border-line">
            <h2>Rekapitulasi per Layanan</h2>
            <p class="text-sm text-muted mt-0.5">{{ \Carbon\Carbon::parse($start)->translatedFormat('d M Y') }} — {{ \Carbon\Carbon::parse($end)->translatedFormat('d M Y') }}</p>
        </div>

        <div class="table-scroll">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="border-b border-line bg-canvas-parchment/70">
                        <th class="table-th">Layanan</th>
                        <th class="table-th">Total</th>
                        <th class="table-th">Terpenuhi</th>
                        <th class="table-th">Belum</th>
                        <th class="table-th w-64">Pemenuhan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($perCategory as $row)
                        <tr class="hover:bg-canvas-parchment/50 transition-colors">
                            <td class="table-td font-semibold text-ink">{{ $row['nama'] }}</td>
                            <td class="table-td text-body">{{ number_format($row['total']) }}</td>
                            <td class="table-td text-teal-brand font-semibold">{{ number_format($row['terpenuhi']) }}</td>
                            <td class="table-td text-orange-500 font-semibold">{{ number_format($row['belum']) }}</td>
                            <td class="table-td">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 rounded-full bg-ink/5 overflow-hidden">
                                        <div class="h-full rounded-full {{ $row['persen'] >= 75 ? 'bg-teal-brand' : ($row['persen'] >= 40 ? 'bg-primary' : 'bg-orange-500') }}" style="width: {{ $row['persen'] }}%"></div>
                                    </div>
                                    <span class="text-xs text-muted font-semibold w-9 text-right">{{ $row['persen'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-muted">
                                <p class="text-sm">Tidak ada data pada periode ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
