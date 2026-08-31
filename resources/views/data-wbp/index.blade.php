<x-app-layout title="Data WBP">
    <x-page-header title="Data Warga Binaan" description="Kelola data warga binaan dan pantau pemenuhan haknya">
        @auth
            @can('create', App\Models\Wbp::class)
                <x-slot:actions>
                    <a href="{{ route('wbp.create') }}" class="btn-primary">
                        <x-icon name="plus" class="w-4 h-4" />
                        Tambah WBP
                    </a>
                </x-slot:actions>
            @endcan
        @endauth
    </x-page-header>

    <div class="card mb-5">
        <form method="GET" action="{{ route('wbp.index') }}" class="p-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-input-label for="q" value="Cari Nama / No. Reg / NIK" />
                <input type="text" id="q" name="q" value="{{ $search }}" class="form-input" placeholder="Ketik kata kunci...">
            </div>

            <div>
                <x-input-label for="blok" value="Blok Kamar" />
                <select id="blok" name="blok" class="form-input">
                    <option value="">Semua Blok</option>
                    @foreach ($blokList as $option)
                        <option value="{{ $option }}" @selected($blok === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="form-input">
                    <option value="">Semua Status</option>
                    <option value="Aktif" @selected($status === 'Aktif')>Aktif</option>
                    <option value="Non-Aktif" @selected($status === 'Non-Aktif')>Non-Aktif</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="btn-secondary w-full">
                    <x-icon name="search" class="w-4 h-4" />
                    Cari
                </button>
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="table-scroll">
            <table class="w-full min-w-[960px]">
                <thead>
                    <tr class="border-b border-line bg-canvas-parchment/70">
                        <th class="table-th">WBP</th>
                        @php
                            $columns = ['no_register' => 'No. Register', 'blok_kamar' => 'Blok', 'agama' => 'Agama', 'status' => 'Status'];
                        @endphp
                        @foreach ($columns as $key => $label)
                            <th class="table-th">
                                <a href="{{ route('wbp.index', array_merge(request()->query(), ['sort' => $key, 'dir' => $sort === $key && $dir === 'asc' ? 'desc' : 'asc'])) }}"
                                   class="inline-flex items-center gap-1 hover:text-ink transition-colors">
                                    {{ $label }}
                                    @if ($sort === $key)
                                        <x-icon :name="$dir === 'asc' ? 'chevron-up' : 'chevron-down'" class="w-3.5 h-3.5" />
                                    @endif
                                </a>
                            </th>
                        @endforeach
                        <th class="table-th">Pemenuhan Hak</th>
                        <th class="table-th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($wbps as $wbp)
                        <tr class="hover:bg-canvas-parchment/50 transition-colors">
                            <td class="table-td">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="size-9 rounded-full bg-ink/5 flex items-center justify-center text-ink text-xs font-semibold shrink-0">
                                        {{ strtoupper(substr($wbp->nama, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-ink truncate max-w-[220px]">{{ $wbp->nama }}</p>
                                        <p class="text-xs text-muted">Reg. {{ $wbp->no_register }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-td text-body">{{ $wbp->no_register }}</td>
                            <td class="table-td">
                                <span class="badge badge-blue">{{ $wbp->blok_kamar }}</span>
                            </td>
                            <td class="table-td text-body">{{ $wbp->agama }}</td>
                            <td class="table-td">
                                <span class="badge {{ $wbp->status === App\Enums\WbpStatus::Aktif ? 'badge-green' : 'badge-gray' }}">
                                    {{ $wbp->status->value }}
                                </span>
                            </td>
                            <td class="table-td">
                                @php $progress = $wbp->progressHak($wbp->monitoringLogs, $totalCategories); @endphp
                                <div class="flex items-center gap-2 min-w-36">
                                    <div class="flex-1 h-1.5 rounded-full bg-ink/5 overflow-hidden">
                                        <div class="h-full rounded-full {{ $progress >= 75 ? 'bg-teal-brand' : ($progress >= 40 ? 'bg-primary' : 'bg-orange-500') }}"
                                             style="width: {{ $progress }}%"></div>
                                    </div>
                                    <span class="text-xs text-muted font-semibold w-8 text-right">{{ $progress }}%</span>
                                </div>
                            </td>
                            <td class="table-td">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('wbp.show', $wbp) }}" class="btn-secondary btn-sm" title="Detail">
                                        <x-icon name="eye" class="w-4 h-4" />
                                    </a>
                                    @can('update', $wbp)
                                        <a href="{{ route('wbp.edit', $wbp) }}" class="btn-secondary btn-sm" title="Edit">
                                            <x-icon name="pencil" class="w-4 h-4" />
                                        </a>
                                    @endcan
                                    @can('delete', $wbp)
                                        <x-confirm-delete-modal title="Hapus Data WBP"
                                                                 message="Data '{{ $wbp->nama }}' akan dihapus. Tindakan ini tidak dapat dibatalkan."
                                                                 buttonLabel="Hapus">
                                            <button type="button" class="btn-danger btn-sm" @click="open('{{ route('wbp.destroy', $wbp) }}')" title="Hapus">
                                                <x-icon name="trash" class="w-4 h-4" />
                                            </button>
                                        </x-confirm-delete-modal>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-muted">
                                <x-icon name="inbox" class="mx-auto w-10 h-10 mb-2 opacity-50" />
                                <p class="text-sm">Tidak ada data WBP yang cocok dengan filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-line">
            {{ $wbps->links() }}
        </div>
    </div>
</x-app-layout>
