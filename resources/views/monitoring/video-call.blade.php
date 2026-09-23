<x-app-layout title="Kunjungan">
    <x-page-header title="Kunjungan" description="Jadwal dan riwayat sesi kunjungan WBP">
        <x-slot:actions>
            <a href="{{ route('monitoring.video-call.export', array_merge(request()->query(), ['tanggal' => $tanggal])) }}" class="btn-secondary">
                <x-icon name="download" class="w-4 h-4" />
                Export Excel
            </a>
        </x-slot:actions>
    </x-page-header>

    @php $kunjunganStatusOptions = \App\Enums\MonitoringStatus::optionsFor($category->slug); @endphp

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
        <div class="card card-padding xl:col-span-2" x-data="editSesi(@js($kunjunganStatusOptions))">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h2>Jadwal Sesi Kunjungan</h2>
                    <p class="text-sm text-muted mt-0.5">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</p>
                </div>

                <form method="GET" action="{{ route('monitoring.video-call') }}" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="q" value="{{ $q }}" class="form-input w-44 sm:!w-52" placeholder="Cari nama WBP..." onchange="this.form.submit()">
                    <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-input w-40 sm:!w-44" onchange="this.form.submit()">
                    <select name="blok" class="form-input w-32 sm:!w-36" onchange="this.form.submit()">
                        <option value="">Semua Blok</option>
                        @foreach ($blokList as $option)
                            <option value="{{ $option }}" @selected($blok === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-secondary btn-sm">
                        <x-icon name="search" class="w-4 h-4" />
                    </button>
                </form>
            </div>

            <div class="table-scroll">
                <table class="w-full min-w-[900px]">
                    <thead>
                        <tr class="border-b border-line bg-canvas-parchment/70">
                            <th class="table-th w-10">No</th>
                            <th class="table-th">Nama WBP</th>
                            <th class="table-th">Blok/Kamar</th>
                            <th class="table-th">Tanggal</th>
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
                                <td class="table-td text-muted">{{ $logs->firstItem() + $loop->index }}</td>
                                <td class="table-td">
                                    <p class="font-semibold text-ink truncate max-w-[220px]">{{ $log->wbp?->nama ?? '—' }}</p>
                                    <p class="text-xs text-muted">Reg. {{ $log->wbp?->no_register ?? '—' }}</p>
                                </td>
                                <td class="table-td">
                                    <span class="badge badge-blue">{{ $log->wbp?->blok_kamar ?? '—' }}</span>
                                </td>
                                <td class="table-td text-body">{{ $log->tanggal->translatedFormat('d M Y') }}</td>
                                <td class="table-td text-body">{{ $log->waktu_mulai ?? '—' }}</td>
                                <td class="table-td">
                                    <span class="badge {{ $log->isTerpenuhi() ? 'badge-green' : 'badge-orange' }}">{{ $log->status->value }}</span>
                                </td>
                                <td class="table-td text-body max-w-xs truncate">{{ $log->keterangan ?? '—' }}</td>
                                <td class="table-td text-muted">{{ $log->user?->name ?? '—' }}</td>
                                <td class="table-td">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button"
                                                class="btn-secondary btn-sm"
                                                title="Edit"
                                                @click="editSesi.open({
                                                    action: @js(route('monitoring.update', $log)),
                                                    status: @js($log->status->value),
                                                    tanggal: @js($log->tanggal->toDateString()),
                                                    waktu_mulai: @js($log->waktu_mulai),
                                                    keterangan: @js($log->keterangan)
                                                }); $dispatch('open-modal', 'edit-sesi')">
                                            <x-icon name="pencil" class="w-4 h-4" />
                                        </button>
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
                                <td colspan="9" class="px-6 py-12 text-center text-muted">
                                    <x-icon name="phone" class="mx-auto w-10 h-10 mb-2 opacity-50" />
                                    <p class="text-sm">Belum ada sesi kunjungan pada tanggal ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-line">
                {{ $logs->links() }}
            </div>

            <x-modal name="edit-sesi" :show="false" maxWidth="md">
                <form :action="editSesi.action" method="POST" class="card-padding">
                    @csrf
                    @method('PATCH')
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h2>Edit Sesi Kunjungan</h2>
                            <p class="text-sm text-muted mt-0.5">Ubah tanggal dan detail sesi</p>
                        </div>
                        <button type="button" class="text-muted hover:text-ink p-1" @click="$dispatch('close-modal', 'edit-sesi')" title="Tutup">
                            <x-icon name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <div x-show="editSesi.show" class="space-y-4">
                        <div>
                            <x-input-label for="edit_status" value="Status" />
                            <select id="edit_status" name="status" x-model="editSesi.form.status" class="form-input" required>
                                @foreach ($kunjunganStatusOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="edit_tanggal" value="Tanggal" />
                            <input type="date" id="edit_tanggal" name="tanggal" x-model="editSesi.form.tanggal" class="form-input" required>
                            <x-input-error :messages="$errors->get('tanggal')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="edit_waktu_mulai" value="Waktu Mulai" />
                            <input type="time" id="edit_waktu_mulai" name="waktu_mulai" x-model="editSesi.form.waktu_mulai" class="form-input">
                            <x-input-error :messages="$errors->get('waktu_mulai')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="edit_keterangan" value="Keterangan" />
                            <textarea id="edit_keterangan" name="keterangan" rows="3" x-model="editSesi.form.keterangan" class="form-input" placeholder="contoh: kunjungan keluarga"></textarea>
                            <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" class="btn-secondary" @click="$dispatch('close-modal', 'edit-sesi')">
                                Batal
                            </button>
                            <button type="submit" class="btn-primary">
                                <x-icon name="check" class="w-4 h-4" />
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </x-modal>
        </div>

        <div class="card card-padding h-fit">
            <h2 class="mb-4">Catat Sesi Baru</h2>

            <form method="POST" action="{{ route('monitoring.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="category_id" value="{{ $category->id }}">
                <input type="hidden" name="status" value="Selesai">

                <div>
                    <x-input-label for="wbp-search" value="Pilih WBP" />
                    <div x-data="wbpSearch(@js(route('monitoring.wbp-search')))" @click.outside="open = false">
                        <input type="hidden" name="wbp_id" :value="selected ? selected.id : ''">

                        <template x-if="!selected">
                            <div class="relative">
                                <input type="text"
                                       id="wbp-search"
                                       x-ref="input"
                                       x-model="query"
                                       class="form-input pr-10"
                                       placeholder="Cari nama / blok / kamar..."
                                       @input="search()"
                                       @focus="search()"
                                       @keydown.escape="open = false">
                                <x-icon name="search" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" />

                                <div x-show="open" x-cloak class="absolute z-20 mt-2 w-full rounded-xl border border-line bg-surface shadow-lg overflow-hidden">
                                    <p x-show="loading" class="px-4 py-3 text-sm text-muted">Mencari...</p>
                                    <p x-show="!loading && error" class="px-4 py-3 text-sm text-red-600" x-text="error"></p>
                                    <ul x-show="!loading && results.length > 0" class="max-h-60 overflow-y-auto divide-y divide-line">
                                        <template x-for="item in results" :key="item.id">
                                            <li>
                                                <button type="button"
                                                        @click="select(item)"
                                                        class="w-full text-left px-4 py-2.5 hover:bg-canvas-parchment transition-colors">
                                                    <p class="font-semibold text-ink text-sm" x-text="item.nama"></p>
                                                    <p class="text-xs text-muted" x-text="'Blok ' + item.blok_kamar + ' · Reg. ' + item.no_register"></p>
                                                </button>
                                            </li>
                                        </template>
                                    </ul>
                                    <p x-show="!loading && !error && results.length === 0" class="px-4 py-3 text-sm text-muted">
                                        Tidak ada WBP yang cocok.
                                    </p>
                                </div>
                            </div>
                        </template>

                        <template x-if="selected">
                            <div class="flex items-center justify-between gap-3 rounded-lg border border-teal-brand/40 bg-teal-brand/5 p-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-ink text-sm truncate" x-text="selected.nama"></p>
                                    <p class="text-xs text-muted" x-text="'Blok ' + selected.blok_kamar + ' · No. Reg. ' + selected.no_register"></p>
                                </div>
                                <button type="button" @click="clear()" class="text-sm text-muted hover:text-red-600 font-medium shrink-0">
                                    Ganti
                                </button>
                            </div>
                        </template>
                    </div>
                    <x-input-error :messages="$errors->get('wbp_id')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="tanggal" value="Tanggal" />
                        <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', $tanggal) }}" class="form-input" required>
                        <x-input-error :messages="$errors->get('tanggal')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="waktu_mulai" value="Waktu Mulai" />
                        <input type="time" id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai') }}" class="form-input">
                        <x-input-error :messages="$errors->get('waktu_mulai')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="keterangan" value="Keterangan" />
                    <textarea id="keterangan" name="keterangan" rows="3" class="form-input" placeholder="contoh: kunjungan keluarga">{{ old('keterangan') }}</textarea>
                </div>

                <button type="submit" class="btn-primary w-full">
                    <x-icon name="plus" class="w-4 h-4" />
                    Simpan Sesi
                </button>
            </form>
        </div>
    </div>
</x-app-layout>