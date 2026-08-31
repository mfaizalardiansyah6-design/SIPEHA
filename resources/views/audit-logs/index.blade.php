<x-app-layout title="Audit Log">
    <x-page-header title="Audit Log" description="Jejak aktivitas perubahan data oleh pengguna" />

    <div class="card mb-5">
        <form method="GET" action="{{ route('audit-logs') }}" class="p-4 flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <x-input-label for="q" value="Cari Aksi / Tabel / Pengguna" />
                <input type="text" id="q" name="q" value="{{ $search }}" class="form-input" placeholder="Ketik kata kunci...">
            </div>
            <button type="submit" class="btn-secondary">
                <x-icon name="search" class="w-4 h-4" />
                Cari
            </button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="table-scroll">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="border-b border-line bg-canvas-parchment/70">
                        <th class="table-th">Waktu</th>
                        <th class="table-th">Pengguna</th>
                        <th class="table-th">Aksi</th>
                        <th class="table-th">Tabel</th>
                        <th class="table-th">Data Baru</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-canvas-parchment/50 transition-colors">
                            <td class="table-td">
                                <p class="text-body">{{ $log->created_at->translatedFormat('d M Y') }}</p>
                                <p class="text-xs text-muted">{{ $log->created_at->format('H:i:s') }}</p>
                            </td>
                            <td class="table-td text-body">{{ $log->user?->name ?? 'Sistem' }}</td>
                            <td class="table-td">
                                <span class="badge {{ $log->action === 'delete' ? 'badge-red' : ($log->action === 'create' ? 'badge-green' : 'badge-blue') }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="table-td">
                                <span class="text-body font-semibold">{{ $log->target_table }}</span>
                                @if ($log->target_id)
                                    <p class="text-xs text-muted">{{ $log->target_id }}</p>
                                @endif
                            </td>
                            <td class="table-td">
                                @if ($log->new_value)
                                    <code class="text-xs text-body bg-canvas px-2 py-1 rounded block truncate max-w-[320px]">{{ collect($log->new_value)->take(4)->map(fn ($value, $key) => "$key: $value")->join(', ') }}</code>
                                @else
                                    <span class="text-xs text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-muted">
                                <x-icon name="shield-check" class="mx-auto w-10 h-10 mb-2 opacity-50" />
                                <p class="text-sm">Belum ada catatan aktivitas.</p>
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
</x-app-layout>
