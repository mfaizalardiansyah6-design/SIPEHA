<x-app-layout title="Peminjaman Buku">
    <x-page-header title="Peminjaman Buku" description="Kelola peminjaman dan pengembalian buku perpustakaan" />

    <div class="grid grid-cols-2 gap-5 lg:grid-cols-3 xl:grid-cols-5">
        <x-stat-card
            icon="book-open"
            tone="blue"
            label="Total Koleksi"
            :value="number_format($totalKoleksi)"
        />

        <x-stat-card
            icon="check-circle"
            tone="teal"
            label="Tersedia"
            :value="number_format($tersedia)"
            value-class="text-teal-brand"
        />

        <x-stat-card
            icon="book"
            tone="blue"
            label="Sedang Dipinjam"
            :value="number_format($dipinjam)"
            value-class="text-primary"
        />

        <x-stat-card
            icon="clock"
            label="Dipinjam Hari Ini"
            :value="number_format($dipinjamHariIni)"
        />

        <x-stat-card
            icon="exclamation-triangle"
            tone="red"
            label="Terlambat"
            :value="number_format($overdue)"
            :value-class="$overdue ? 'text-red-600' : 'text-ink'"
        />
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3 mt-6">
        <div class="card card-padding xl:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h2>Riwayat Peminjaman</h2>
                    <p class="text-sm text-muted mt-0.5">Seluruh transaksi peminjaman buku</p>
                </div>

                <form method="GET" action="{{ route('monitoring.buku') }}" class="flex items-center gap-2">
                    <input type="text" name="q" value="{{ $search }}" class="form-input !w-56" placeholder="Cari judul / nama WBP...">
                    <button type="submit" class="btn-secondary btn-sm">
                        <x-icon name="search" class="w-4 h-4" />
                    </button>
                </form>
            </div>

            <div class="table-scroll">
                <table class="w-full min-w-[800px]">
                    <thead>
                        <tr class="border-b border-line bg-canvas-parchment/70">
                            <th class="table-th">Judul Buku</th>
                            <th class="table-th">WBP</th>
                            <th class="table-th">Dipinjam</th>
                            <th class="table-th">Status</th>
                            <th class="table-th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($borrows as $borrow)
                            <tr class="hover:bg-canvas-parchment/50 transition-colors">
                                <td class="table-td">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="size-9 rounded-lg bg-ink/5 flex items-center justify-center text-ink shrink-0">
                                            <x-icon name="book-open" class="w-5 h-5" />
                                        </div>
                                        <span class="font-semibold text-ink truncate max-w-[260px]">{{ $borrow->judul_buku }}</span>
                                    </div>
                                </td>
                                <td class="table-td text-body">{{ $borrow->wbp?->nama ?? '—' }}</td>
                                <td class="table-td">
                                    <p class="text-body">{{ $borrow->tanggal_pinjam->translatedFormat('d M Y') }}</p>
                                    @if ($borrow->tanggal_kembali)
                                        <p class="text-xs text-muted">Jatuh tempo {{ $borrow->tanggal_kembali->translatedFormat('d M Y') }}</p>
                                    @endif
                                </td>
                                <td class="table-td">
                                    <span class="badge {{ $borrow->status === App\Enums\BorrowStatus::Dikembalikan ? 'badge-green' : ($borrow->isOverdue() ? 'badge-red' : 'badge-blue') }}">
                                        {{ $borrow->status->value }}{{ $borrow->isOverdue() ? ' — terlambat' : '' }}
                                    </span>
                                </td>
                                <td class="table-td">
                                    <div class="flex items-center justify-end">
                                        @if ($borrow->status === App\Enums\BorrowStatus::Dipinjam)
                                            <form method="POST" action="{{ route('monitoring.buku.update-status', $borrow) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ App\Enums\BorrowStatus::Dikembalikan->value }}">
                                                <button type="submit" class="btn-secondary btn-sm">
                                                    <x-icon name="check" class="w-4 h-4" />
                                                    Kembalikan
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-muted">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-muted">
                                    <x-icon name="book-open" class="mx-auto w-10 h-10 mb-2 opacity-50" />
                                    <p class="text-sm">Belum ada data peminjaman buku.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-line">
                {{ $borrows->links() }}
            </div>
        </div>

        <div class="card card-padding h-fit">
            <h2 class="mb-4">Catat Peminjaman</h2>

            <form method="POST" action="{{ route('monitoring.buku.store') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="wbp_id" value="WBP" />
                    <select id="wbp_id" name="wbp_id" class="form-input" required>
                        <option value="">Pilih WBP</option>
                        @foreach ($wbps as $wbp)
                            <option value="{{ $wbp->id }}">{{ $wbp->nama }} — {{ $wbp->no_register }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('wbp_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="judul_buku" value="Judul Buku" />
                    <input type="text" id="judul_buku" name="judul_buku" value="{{ old('judul_buku') }}" class="form-input" required>
                    <x-input-error :messages="$errors->get('judul_buku')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="tanggal_pinjam" value="Tanggal Pinjam" />
                        <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', now()->toDateString()) }}" class="form-input" required>
                        <x-input-error :messages="$errors->get('tanggal_pinjam')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="tanggal_kembali" value="Jatuh Tempo" />
                        <input type="date" id="tanggal_kembali" name="tanggal_kembali" value="{{ old('tanggal_kembali', now()->addDays(7)->toDateString()) }}" class="form-input" required>
                        <x-input-error :messages="$errors->get('tanggal_kembali')" class="mt-2" />
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full">
                    <x-icon name="plus" class="w-4 h-4" />
                    Catat Peminjaman
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
