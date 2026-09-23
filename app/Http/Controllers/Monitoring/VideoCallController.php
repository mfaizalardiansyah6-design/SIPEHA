<?php

namespace App\Http\Controllers\Monitoring;

use App\Enums\WbpStatus;
use App\Http\Controllers\Controller;
use App\Models\MonitoringLog;
use App\Models\ServiceCategory;
use App\Models\Wbp;
use App\Services\ExcelExporter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VideoCallController extends Controller
{
    public function index(Request $request): View
    {
        $category = ServiceCategory::where('slug', 'video-call')->firstOrFail();
        $tanggal = $request->date('tanggal')?->toDateString() ?? today()->toDateString();
        $blok = $request->string('blok')->trim()->value();
        $q = $request->string('q')->trim()->value();

        $logs = MonitoringLog::with(['wbp', 'user'])
            ->whereHas('wbp')
            ->where('category_id', $category->id)
            ->whereDate('tanggal', $tanggal)
            ->when($q !== '', fn ($query) => $query->whereHas('wbp', fn ($query) => $query->where(function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('no_register', 'like', "%{$q}%");
            })))
            ->when($blok !== '', fn ($query) => $query->whereHas('wbp', fn ($query) => $query->where('blok_kamar', $blok)))
            ->orderByRaw('waktu_mulai IS NULL, waktu_mulai ASC')
            ->paginate(12)
            ->withQueryString();

        $daily = MonitoringLog::where('category_id', $category->id)
            ->whereDate('tanggal', $tanggal)
            ->get();

        $totalWbp = Wbp::where('status', WbpStatus::Aktif->value)->count();
        $selesai = $daily->filter->isTerpenuhi()->count();
        $belum = $daily->count() - $selesai;

        $blokList = Wbp::select('blok_kamar')->distinct()->orderBy('blok_kamar')->pluck('blok_kamar');

        return view('monitoring.video-call', compact('category', 'logs', 'tanggal', 'blok', 'q', 'blokList', 'totalWbp', 'selesai', 'belum'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        $category = ServiceCategory::where('slug', 'video-call')->firstOrFail();
        $tanggal = $request->date('tanggal')?->toDateString() ?? today()->toDateString();

        $logs = MonitoringLog::with('wbp')
            ->whereHas('wbp')
            ->where('category_id', $category->id)
            ->whereDate('tanggal', $tanggal)
            ->orderBy('waktu_mulai')
            ->get();

        $rows = $logs->map(fn (MonitoringLog $log) => [
            $log->wbp?->nama,
            $log->wbp?->no_register,
            $log->wbp?->blok_kamar,
            $log->waktu_mulai,
            $log->status->value,
            $log->keterangan,
        ])->toArray();

        return app(ExcelExporter::class)->download(
            "kunjungan-{$tanggal}.xlsx",
            ['Nama WBP', 'No. Register', 'Blok', 'Waktu Mulai', 'Status', 'Keterangan'],
            $rows,
            'Kunjungan',
        );
    }
}
