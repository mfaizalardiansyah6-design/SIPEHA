<x-app-layout title="Alat Ibadah">
    @php
        $pieLabels = $agamaDist->pluck('agama');
        $pieValues = $agamaDist->pluck('total');
        $pieColors = ['#0066cc', '#2997ff', '#1d1d1f', '#7a7a7a', '#c8c8cc', '#4a7fb0'];

        $agamaChartData = [
            'chart' => ['type' => 'donut', 'height' => 260, 'fontFamily' => 'Inter'],
            'labels' => $pieLabels,
            'series' => $pieValues,
            'colors' => $pieColors,
            'legend' => ['position' => 'bottom'],
            'stroke' => ['width' => 0],
            'dataLabels' => ['enabled' => false],
            'plotOptions' => ['pie' => ['donut' => ['size' => '70%']]],
        ];
    @endphp

    <script type="application/json" id="agama-chart-data">{!! Js::from($agamaChartData) !!}</script>

    <x-page-header title="Alat Ibadah" description="Distribusi dan penyerahan alat ibadah sesuai agama WBP" />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card card-padding xl:col-span-2">
            <div class="mb-4">
                <h2>Penyerahan Alat Ibadah</h2>
                <p class="text-sm text-muted mt-0.5">Status terakhir per WBP</p>
            </div>

            <div class="table-scroll">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-line bg-canvas-parchment/70">
                            <th class="table-th">WBP</th>
                            <th class="table-th">Agama</th>
                            <th class="table-th">Status Terakhir</th>
                            <th class="table-th">Perbarui</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($wbps as $wbp)
                            @php $log = $logs->get($wbp->id); @endphp
                            <tr class="hover:bg-canvas-parchment/50 transition-colors align-middle">
                                <td class="table-td">
                                    <p class="font-semibold text-ink truncate max-w-[240px]">{{ $wbp->nama }}</p>
                                    <p class="text-xs text-muted">{{ $wbp->blok_kamar }}</p>
                                </td>
                                <td class="table-td text-body">{{ $wbp->agama }}</td>
                                <td class="table-td">
                                    @if ($log)
                                        <span class="badge {{ $log->isTerpenuhi() ? 'badge-green' : 'badge-orange' }}">{{ $log->status->value }}</span>
                                        @if ($log->keterangan)
                                            <p class="text-xs text-muted mt-1">Alat: {{ $log->keterangan }}</p>
                                        @endif
                                    @else
                                        <span class="badge badge-gray">Belum tercatat</span>
                                    @endif
                                </td>
                                <td class="table-td">
                                    <form method="POST" action="{{ route('monitoring.set-status') }}" class="flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="wbp_id" value="{{ $wbp->id }}">
                                        <input type="hidden" name="category_id" value="{{ $category->id }}">
                                        <input type="hidden" name="tanggal" value="{{ now()->toDateString() }}">
                                        <select name="status" class="form-input !w-32 !py-1.5 text-xs">
                                            <option value="">— Pilih —</option>
                                            @foreach ($statusOptions as $option)
                                                <option value="{{ $option }}" @selected($log && $log->status->value === $option)>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="keterangan" value="{{ $log?->keterangan }}" class="form-input !w-32 !py-1.5 text-xs" placeholder="Jenis alat">
                                        <button type="submit" class="btn-primary btn-sm">
                                            <x-icon name="check" class="w-4 h-4" />
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card card-padding h-fit">
            <div class="mb-2 flex items-center justify-between">
                <h2>Sebaran Agama</h2>
                <span class="text-xs text-muted">{{ $total }} WBP</span>
            </div>
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-muted">Diterima: <strong class="text-teal-brand font-semibold">{{ $diterima }}</strong></p>
                <p class="text-sm text-muted">Pemenuhan: <strong class="text-ink font-semibold">{{ $persen }}%</strong></p>
            </div>
            <div x-data="chart('agama-chart-data')">
                <div x-ref="container"></div>
            </div>
        </div>
    </div>
</x-app-layout>
