<x-app-layout title="Pemeriksaan Kesehatan">
    <x-page-header title="Pemeriksaan Kesehatan" description="Pemantauan pemeriksaan kesehatan warga binaan">
        <x-slot:actions>
            <form method="GET" action="{{ route('monitoring.pemeriksaan-kesehatan') }}" class="flex items-center gap-2">
                <input type="hidden" name="blok" value="{{ $blok }}">
                <input type="hidden" name="q" value="{{ $q }}">
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-input w-full sm:!w-44" onchange="this.form.submit()">
            </form>
        </x-slot:actions>
    </x-page-header>

    @php
        $tablePayload = [
            'wbps' => $payload,
            'blokList' => $blokList,
            'setStatusUrl' => route('monitoring.set-status'),
            'categoryId' => $category->id,
            'tanggal' => $tanggal,
            'selectedBlok' => $blok,
            'wbpQuery' => $q,
        ];
    @endphp

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3" x-data="kesehatanTable(@js($tablePayload))">
        <div class="card card-padding xl:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                <div>
                    <h2>Status Pemeriksaan {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}</h2>
                    <p class="text-sm text-muted mt-0.5" x-text="filtered.length + ' dari ' + wbps.length + ' WBP aktif'"></p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="badge badge-green">Sudah: {{ $sudah }}</span>
                    <span class="badge badge-orange">Belum: {{ $belum }}</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-4">
                <form class="flex flex-wrap items-center gap-2 flex-1" @submit.prevent>
                    <div class="relative" @click.outside="blokOpen = false">
                        <input type="text"
                               x-model="blokQuery"
                               class="form-input w-full sm:!w-40"
                               placeholder="Cari blok..."
                               @input="filterBlok(); blokOpen = true"
                               @focus="blokOpen = true"
                               @keydown.escape="blokOpen = false">
                        <x-icon name="search" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" />

                        <div x-show="blokOpen" x-cloak class="absolute z-20 mt-2 w-full rounded-xl border border-line bg-surface shadow-lg overflow-hidden left-0 sm:left-auto sm:right-0">
                            <ul x-show="blokResults.length > 0" class="max-h-60 overflow-y-auto divide-y divide-line">
                                <li>
                                    <button type="button" @click="clearBlok()" class="w-full text-left px-4 py-2.5 text-sm hover:bg-canvas-parchment transition-colors">
                                        Semua Blok
                                    </button>
                                </li>
                                <template x-for="b in blokResults" :key="b">
                                    <li>
                                        <button type="button" @click="selectBlok(b)" class="w-full text-left px-4 py-2.5 text-sm hover:bg-canvas-parchment transition-colors flex items-center justify-between gap-2">
                                            <span class="text-ink" x-text="b"></span>
                                            <span x-show="b === selectedBlok" class="text-primary text-xs">Dipilih</span>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                            <p x-show="blokResults.length === 0" class="px-4 py-3 text-sm text-muted">Tidak ada blok yang cocok.</p>
                        </div>
                    </div>

                    <div class="relative flex-1 min-w-40">
                        <input type="text" x-model="wbpQuery" class="form-input w-full sm:!w-48" placeholder="Cari WBP / No. Reg..." @input="onWbpQuery()">
                        <x-icon name="search" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" />
                    </div>

                    <template x-if="selectedBlok">
                        <span class="badge badge-blue">
                            <x-icon name="home" class="w-3.5 h-3.5" />
                            <span x-text="selectedBlok"></span>
                            <button type="button" @click="clearBlok()" class="hover:text-red-500 transition-colors" title="Hapus filter blok">
                                <x-icon name="x" class="w-3.5 h-3.5" />
                            </button>
                        </span>
                    </template>
                </form>
            </div>

            <div class="table-scroll">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-line bg-canvas-parchment/70">
                            <th class="table-th">WBP</th>
                            <th class="table-th">Blok</th>
                            <th class="table-th">Status</th>
                            <th class="table-th">Tandai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        <template x-for="w in filtered" :key="w.id">
                            <tr class="hover:bg-canvas-parchment/50 transition-colors">
                                <td class="table-td">
                                    <p class="font-semibold text-ink truncate max-w-[240px]" x-text="w.nama"></p>
                                    <p class="text-xs text-muted" x-text="w.no_register"></p>
                                </td>
                                <td class="table-td">
                                    <span class="badge badge-blue" x-text="w.blok_kamar"></span>
                                </td>
                                <td class="table-td">
                                    <template x-if="w.status">
                                        <span class="badge" :class="w.fulfilled ? 'badge-green' : 'badge-orange'" x-text="w.status_label"></span>
                                    </template>
                                    <template x-if="!w.status">
                                        <span class="badge badge-gray">Belum</span>
                                    </template>
                                </td>
                                <td class="table-td">
                                    <form method="POST" :action="setStatusUrl">
                                        @csrf
                                        <input type="hidden" name="wbp_id" :value="w.id">
                                        <input type="hidden" name="category_id" :value="categoryId">
                                        <input type="hidden" name="tanggal" :value="tanggal">
                                        <select name="status" x-model="w.status" class="form-input !w-36 !py-1.5 text-xs" @change="$event.target.value ? $event.target.form.submit() : null">
                                            <option value="">— Pilih —</option>
                                            @foreach ($statusOptions as $option)
                                                <option value="{{ $option }}">{{ \App\Enums\MonitoringStatus::from($option)->labelKesehatan() }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        </template>
                        <template x-if="filtered.length === 0">
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-muted">
                                    <x-icon name="activity" class="mx-auto w-10 h-10 mb-2 opacity-50" />
                                    <p class="text-sm">Tidak ada WBP yang cocok dengan filter.</p>
                                    <p class="text-xs mt-1" x-show="selectedBlok" x-text="'Blok ' + selectedBlok + ' tidak memiliki WBP aktif.'"></p>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card card-padding" x-data="kesehatanForm(@js(route('monitoring.wbp-search')), @js($statusOptions), @js($blokList))">
                <h2 class="mb-1">Catat Pemeriksaan Baru</h2>
                <p class="text-sm text-muted mb-4">Cari blok, pilih WBP, lalu simpan status pemeriksaan kesehatan.</p>

                <form method="POST" :action="@js(route('monitoring.set-status'))" x-ref="form" class="space-y-4">
                    @csrf
                    <input type="hidden" name="category_id" value="{{ $category->id }}">
                    <input type="hidden" name="wbp_id" :value="selectedWbp ? selectedWbp.id : ''">

                    <div>
                        <x-input-label for="kesehatan-blok" value="Blok/Kamar" />
                        <div class="relative" @click.outside="blokOpen = false">
                            <template x-if="!selectedBlok">
                                <input type="text"
                                       id="kesehatan-blok"
                                       x-model="blokQuery"
                                       class="form-input pr-10"
                                       placeholder="Cari blok..."
                                       @input="filterBlok(); blokOpen = true"
                                       @focus="blokOpen = true"
                                       @keydown.escape="blokOpen = false">
                            </template>
                            <template x-if="!selectedBlok">
                                <x-icon name="search" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" />
                            </template>

                            <div x-show="blokOpen && !selectedBlok" x-cloak class="absolute z-20 mt-2 w-full rounded-xl border border-line bg-surface shadow-lg overflow-hidden">
                                <ul x-show="blokResults.length > 0" class="max-h-60 overflow-y-auto divide-y divide-line">
                                    <template x-for="b in blokResults" :key="b">
                                        <li>
                                            <button type="button" @click="selectBlok(b)" class="w-full text-left px-4 py-2.5 text-sm hover:bg-canvas-parchment transition-colors">
                                                <p class="font-semibold text-ink" x-text="b"></p>
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                                <p x-show="blokResults.length === 0" class="px-4 py-3 text-sm text-muted">Tidak ada blok yang cocok.</p>
                            </div>

                            <template x-if="selectedBlok">
                                <div class="flex items-center justify-between gap-3 rounded-lg border border-primary/40 bg-primary/5 p-3">
                                    <p class="font-semibold text-ink text-sm" x-text="'Blok ' + selectedBlok"></p>
                                    <button type="button" @click="clearBlok()" class="text-sm text-muted hover:text-red-600 font-medium shrink-0">
                                        Ganti
                                    </button>
                                </div>
                            </template>
                        </div>
                        <p x-show="blokError" x-cloak class="mt-2 text-sm text-red-600">Tentukan blok terlebih dahulu.</p>
                    </div>

                    <div>
                        <x-input-label for="kesehatan-wbp" value="Pilih WBP" />
                        <div class="relative" @click.outside="wbpOpen = false">
                            <template x-if="!selectedWbp">
                                <input type="text"
                                       id="kesehatan-wbp"
                                       x-model="wbpQuery"
                                       class="form-input pr-10"
                                       :placeholder="selectedBlok ? 'Cari nama / No. Register...' : 'Pilih blok terlebih dahulu'"
                                       :disabled="!selectedBlok"
                                       @input="searchWbp()"
                                       @focus="searchWbp()"
                                       @keydown.escape="wbpOpen = false">
                            </template>
                            <template x-if="!selectedWbp">
                                <x-icon name="search" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" />
                            </template>

                            <div x-show="wbpOpen && !selectedWbp" x-cloak class="absolute z-20 mt-2 w-full rounded-xl border border-line bg-surface shadow-lg overflow-hidden">
                                <p x-show="loading" class="px-4 py-3 text-sm text-muted">Mencari...</p>
                                <p x-show="!loading && error" class="px-4 py-3 text-sm text-red-600" x-text="error"></p>
                                <ul x-show="!loading && wbpResults.length > 0" class="max-h-60 overflow-y-auto divide-y divide-line">
                                    <template x-for="item in wbpResults" :key="item.id">
                                        <li>
                                            <button type="button" @click="selectWbp(item)" class="w-full text-left px-4 py-2.5 hover:bg-canvas-parchment transition-colors">
                                                <p class="font-semibold text-ink text-sm" x-text="item.nama"></p>
                                                <p class="text-xs text-muted" x-text="'Blok ' + item.blok_kamar + ' · Reg. ' + item.no_register"></p>
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                                <p x-show="!loading && !error && wbpResults.length === 0" class="px-4 py-3 text-sm text-muted">Tidak ada WBP yang cocok.</p>
                            </div>

                            <template x-if="selectedWbp">
                                <div class="flex items-center justify-between gap-3 rounded-lg border border-teal-brand/40 bg-teal-brand/5 p-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-ink text-sm truncate" x-text="selectedWbp.nama"></p>
                                        <p class="text-xs text-muted" x-text="'Blok ' + selectedWbp.blok_kamar + ' · No. Reg. ' + selectedWbp.no_register"></p>
                                    </div>
                                    <button type="button" @click="clearWbp()" class="text-sm text-muted hover:text-red-600 font-medium shrink-0">
                                        Ganti
                                    </button>
                                </div>
                            </template>
                        </div>
                        <p x-show="wbpError" x-cloak class="mt-2 text-sm text-red-600">
                            <span x-text="selectedBlok ? 'Pilih WBP terlebih dahulu.' : 'Tentukan blok terlebih dahulu.'"></span>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="kesehatan-tanggal" value="Tanggal" />
                            <input type="date" id="kesehatan-tanggal" name="tanggal" value="{{ $tanggal }}" class="form-input" required>
                        </div>
                        <div>
                            <x-input-label for="kesehatan-status" value="Status" />
                            <select id="kesehatan-status" name="status" x-model="status" class="form-input" required>
                                @foreach ($statusOptions as $option)
                                    <option value="{{ $option }}">{{ \App\Enums\MonitoringStatus::from($option)->labelKesehatan() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="kesehatan-keterangan" value="Keterangan" />
                        <textarea id="kesehatan-keterangan" name="keterangan" rows="2" class="form-input" placeholder="opsional"></textarea>
                    </div>

                    <button type="button" @click="submit()" class="btn-primary w-full">
                        <x-icon name="plus" class="w-4 h-4" />
                        Simpan Pemeriksaan
                    </button>
                </form>
            </div>

            <div class="card card-padding">
                <h2 class="mb-4">Ringkasan Pemeriksaan</h2>

                <div class="mb-4">
                    <div class="flex items-end justify-between mb-2">
                        <span class="text-sm text-muted">Sudah</span>
                        <span class="text-lg font-semibold text-ink" x-text="persen + '%'"></span>
                    </div>
                    <div class="h-2 rounded-full bg-ink/5 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-300"
                             :class="persen >= 75 ? 'bg-teal-brand' : (persen >= 40 ? 'bg-primary' : 'bg-orange-500')"
                             :style="'width: ' + persen + '%'"></div>
                    </div>
                </div>

                <dl class="space-y-3 border-t border-line pt-4">
                    <div class="flex justify-between">
                        <dt class="text-sm text-muted">Sudah</dt>
                        <dd class="text-sm font-semibold text-ink" x-text="selesai + ' WBP'"></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-muted">Belum</dt>
                        <dd class="text-sm font-semibold text-ink" x-text="proses + ' WBP'"></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-muted">Total WBP Aktif</dt>
                        <dd class="text-sm font-semibold text-ink" x-text="total + ' WBP'"></dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>