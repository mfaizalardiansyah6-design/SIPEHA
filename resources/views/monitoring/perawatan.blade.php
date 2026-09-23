<x-app-layout :title="$category->nama_layanan">
    <x-page-header :title="'Perawatan Diri — '.$category->nama_layanan"
                   description="Pantau pemenuhan hak perawatan diri seluruh WBP">

        <x-slot:actions>
            @php $tabs = ['potong-rambut' => 'Potong Rambut', 'potong-kuku' => 'Potong Kuku']; @endphp
            <div class="flex flex-wrap items-center justify-end gap-2">
                <form method="GET" action="{{ route('monitoring.perawatan') }}" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-input w-full sm:!w-44" onchange="this.form.submit()">
                </form>
                <div class="flex items-center gap-1 rounded-lg bg-ink/5 p-1 overflow-x-auto max-w-full">
                    @foreach ($tabs as $slug => $label)
                        <a href="{{ route('monitoring.perawatan', ['tab' => $slug, 'tanggal' => $tanggal]) }}"
                           class="px-3 py-1.5 rounded-md text-xs font-semibold whitespace-nowrap transition-colors {{ $tab === $slug ? 'bg-surface text-ink border border-line' : 'text-muted hover:text-body' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="card card-padding mb-6">
<div class="flex flex-wrap items-center gap-6">
                <div class="flex-1 min-w-56">
                    <div class="flex items-end justify-between mb-2">
                        <div>
                            <p class="text-sm font-semibold text-ink">{{ $category->nama_layanan }}</p>
                            <p class="text-xs text-muted mt-0.5">Status per {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}</p>
                        </div>
                        <span class="text-lg font-semibold text-ink">{{ $persen }}%</span>
                    </div>
                <div class="h-2 rounded-full bg-ink/5 overflow-hidden">
                    <div class="h-full rounded-full {{ $persen >= 75 ? 'bg-teal-brand' : ($persen >= 40 ? 'bg-primary' : 'bg-orange-500') }}" style="width: {{ $persen }}%"></div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center">
                    <p class="text-xl font-semibold text-ink">{{ $fulfilled }}</p>
                    <p class="text-xs text-muted">Selesai</p>
                </div>
                <div class="text-center">
                    <p class="text-xl font-semibold text-ink">{{ $total - $fulfilled }}</p>
                    <p class="text-xs text-muted">Belum</p>
                </div>
                <div class="text-center">
                    <p class="text-xl font-semibold text-ink">{{ $total }}</p>
                    <p class="text-xs text-muted">Total</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="table-scroll">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-line bg-canvas-parchment/70">
                        <th class="table-th">WBP</th>
                        <th class="table-th">Blok</th>
                        <th class="table-th">Status {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}</th>
                        <th class="table-th">Perbarui Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach ($wbps as $wbp)
                        @php $log = $logs->get($wbp->id); @endphp
                        <tr class="hover:bg-canvas-parchment/50 transition-colors">
                            <td class="table-td">
                                <p class="font-semibold text-ink truncate max-w-[240px]">{{ $wbp->nama }}</p>
                                <p class="text-xs text-muted">{{ $wbp->no_register }}</p>
                            </td>
                            <td class="table-td">
                                <span class="badge badge-blue">{{ $wbp->blok_kamar }}</span>
                            </td>
                            <td class="table-td">
                                @if ($log)
                                    <span class="badge {{ $log->isTerpenuhi() ? 'badge-green' : 'badge-orange' }}">{{ $log->status->value }}</span>
                                    <p class="text-xs text-muted mt-1">{{ $log->tanggal->translatedFormat('d M Y') }}</p>
                                @else
                                    <span class="badge badge-gray">Belum tercatat</span>
                                @endif
                            </td>
                            <td class="table-td">
                                <form method="POST" action="{{ route('monitoring.set-status') }}" class="flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="wbp_id" value="{{ $wbp->id }}">
                                    <input type="hidden" name="category_id" value="{{ $category->id }}">
                                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                                    <select name="status" class="form-input !w-32 !py-1.5 text-xs" onchange="this.form.submit()">
                                        <option value="">— Pilih —</option>
                                        @foreach ($statusOptions as $option)
                                            <option value="{{ $option }}" @selected($log && $log->status->value === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
