<x-app-layout title="Jadwal Pelayanan">
    <x-page-header title="Jadwal Pelayanan" description="Agenda harian dan kalender sesi layanan WBP">
        <x-slot:actions>
            <form method="GET" action="{{ route('jadwal') }}" class="flex items-center gap-2">
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-input w-full sm:!w-44" onchange="this.form.submit()">
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card card-padding xl:col-span-2">
            <div class="mb-4">
                <h2>Kalender Sesi Layanan</h2>
                <p class="text-sm text-muted mt-0.5">Jumlah sesi tercatat per tanggal (± 45 hari)</p>
            </div>

            <div x-data="fullCalendar('calendar-events')" class="fc-custom">
                <div x-ref="container"></div>
            </div>

            <script type="application/json" id="calendar-events">
                {!! $events !!}
            </script>
        </div>

        <div class="space-y-6">
            <div class="card overflow-hidden">
                <div class="px-6 pt-5 pb-4 border-b border-line">
                    <h2>Agenda {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}</h2>
                    <p class="text-sm text-muted mt-0.5">{{ $todayLogs->count() }} sesi tercatat</p>
                </div>

                @if ($todayLogs->isEmpty())
                    <div class="px-6 py-10 text-center">
                        <x-icon name="calendar" class="mx-auto w-10 h-10 mb-2 text-line-dark" />
                        <p class="text-sm text-muted">Tidak ada sesi pada tanggal ini.</p>
                    </div>
                @else
                    <div class="p-4 space-y-2.5 max-h-[420px] overflow-y-auto">
                        @foreach ($todayLogs as $log)
                            <div class="flex items-center gap-3 rounded-lg border border-line px-4 py-3">
                                <div class="w-10 shrink-0 text-center">
                                    <p class="text-xs font-semibold text-primary">{{ $log->waktu_mulai ?? '—:—' }}</p>
                                </div>
                                <div class="size-8 rounded-full bg-ink/5 flex items-center justify-center text-ink shrink-0">
                                    <x-icon name="video-camera" class="w-4 h-4" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-ink truncate">{{ $log->wbp->nama }}</p>
                                    <p class="text-xs text-muted">{{ $log->category->nama_layanan }}</p>
                                </div>
                                <span class="badge shrink-0 {{ $log->isTerpenuhi() ? 'badge-green' : 'badge-orange' }}">{{ $log->status->value }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card card-padding">
                <h2 class="mb-3">Pelayanan Terbaru</h2>
                <dl class="space-y-2 text-sm">
                    @foreach ($todayLogs->take(4) as $log)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-body truncate">{{ $log->wbp->nama }}</dt>
                            <dd class="text-muted shrink-0">{{ $log->category->nama_layanan }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
