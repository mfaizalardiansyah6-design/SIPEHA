<x-app-layout title="Layanan Laundry">
    <x-page-header title="Layanan Laundry" description="Pantau pemenuhan hak layanan laundry warga binaan">
        <x-slot:actions>
            <form method="GET" action="{{ route('monitoring.laundry') }}" class="flex items-center gap-2">
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-input w-full sm:!w-44" onchange="this.form.submit()">
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card card-padding xl:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                <div>
                    <h2>Status Laundry {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}</h2>
                    <p class="text-sm text-muted mt-0.5">Perbarui status laundry setiap WBP</p>
                </div>

                <div class="flex items-center gap-2">
                    @foreach ($distribusi as $row)
                        <span class="badge {{ $row->status->value === 'Selesai' ? 'badge-green' : 'badge-blue' }}">
                            {{ $row->status->value }}: {{ $row->total }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="table-scroll">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-line bg-canvas-parchment/70">
                            <th class="table-th">WBP</th>
                            <th class="table-th">Blok</th>
                            <th class="table-th">Status Terakhir</th>
                            <th class="table-th">Perbarui</th>
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
                                        <span class="badge {{ $log->isTerpenuhi() ? 'badge-green' : 'badge-blue' }}">{{ $log->status->value }}</span>
                                    @else
                                        <span class="badge badge-gray">Belum</span>
                                    @endif
                                </td>
                                <td class="table-td">
                                    <form method="POST" action="{{ route('monitoring.set-status') }}">
                                        @csrf
                                        <input type="hidden" name="wbp_id" value="{{ $wbp->id }}">
                                        <input type="hidden" name="category_id" value="{{ $category->id }}">
                                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                                        <select name="status" class="form-input !w-36 !py-1.5 text-xs" onchange="this.form.submit()">
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

        <div class="card card-padding h-fit">
            <h2 class="mb-4">Ringkasan Laundry</h2>

            <div class="mb-4">
                <div class="flex items-end justify-between mb-2">
                    <span class="text-sm text-muted">Selesai</span>
                    <span class="text-lg font-semibold text-ink">{{ $persen }}%</span>
                </div>
                <div class="h-2 rounded-full bg-ink/5 overflow-hidden">
                    <div class="h-full rounded-full {{ $persen >= 75 ? 'bg-teal-brand' : ($persen >= 40 ? 'bg-primary' : 'bg-orange-500') }}" style="width: {{ $persen }}%"></div>
                </div>
            </div>

            <dl class="space-y-3 border-t border-line pt-4">
                <div class="flex justify-between">
                    <dt class="text-sm text-muted">Selesai</dt>
                    <dd class="text-sm font-semibold text-ink">{{ $selesai }} WBP</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-muted">Dalam Proses</dt>
                    <dd class="text-sm font-semibold text-ink">{{ $total - $selesai }} WBP</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-muted">Total WBP Aktif</dt>
                    <dd class="text-sm font-semibold text-ink">{{ $total }} WBP</dd>
                </div>
            </dl>
        </div>
    </div>
</x-app-layout>