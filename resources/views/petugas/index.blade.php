<x-app-layout title="Data Petugas">
    <x-page-header title="Data Petugas" description="Kelola akun petugas pemantau layanan WBP">
        <x-slot:actions>
            <a href="{{ route('petugas.create') }}" class="btn-primary">
                <x-icon name="plus" class="w-4 h-4" />
                Tambah Petugas
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-5">
        <form method="GET" action="{{ route('petugas.index') }}" class="p-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2">
                <x-input-label for="q" value="Cari Nama / NIP / Email" />
                <input type="text" id="q" name="q" value="{{ $search }}" class="form-input" placeholder="Ketik kata kunci...">
            </div>

            <div>
                <x-input-label for="role" value="Peran" />
                <select id="role" name="role" class="form-input">
                    <option value="">Semua Peran</option>
                    @foreach (App\Enums\UserRole::cases() as $option)
                        <option value="{{ $option->value }}" @selected($role === $option->value)>{{ $option->label() }}</option>
                    @endforeach
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
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="border-b border-line bg-canvas-parchment/70">
                        <th class="table-th">Nama</th>
                        <th class="table-th">NIP</th>
                        <th class="table-th">Jabatan</th>
                        <th class="table-th">Peran</th>
                        <th class="table-th">Email</th>
                        <th class="table-th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($petugas as $user)
                        <tr class="hover:bg-canvas-parchment/50 transition-colors">
                            <td class="table-td">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="size-9 rounded-full bg-ink/5 flex items-center justify-center text-ink text-xs font-semibold shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-ink truncate max-w-[240px]">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="table-td text-body">{{ $user->nip }}</td>
                            <td class="table-td text-body">{{ $user->jabatan ?? '—' }}</td>
                            <td class="table-td">
                                <span class="badge {{ $user->isAdmin() ? 'badge-blue' : 'badge-green' }}">
                                    {{ $user->role->label() }}
                                </span>
                            </td>
                            <td class="table-td text-body">{{ $user->email }}</td>
                            <td class="table-td">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('petugas.edit', $user) }}" class="btn-secondary btn-sm" title="Edit">
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </a>
                                    @can('delete', $user)
                                        <x-confirm-delete-modal title="Hapus Petugas"
                                                                 message="Akun '{{ $user->name }}' akan dihapus permanen."
                                                                 buttonLabel="Hapus">
                                            <button type="button" class="btn-danger btn-sm" @click="open('{{ route('petugas.destroy', $user) }}')" title="Hapus">
                                                <x-icon name="trash" class="w-4 h-4" />
                                            </button>
                                        </x-confirm-delete-modal>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-muted">
                                <x-icon name="inbox" class="mx-auto w-10 h-10 mb-2 opacity-50" />
                                <p class="text-sm">Tidak ada petugas yang cocok dengan pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-line">
            {{ $petugas->links() }}
        </div>
    </div>
</x-app-layout>
