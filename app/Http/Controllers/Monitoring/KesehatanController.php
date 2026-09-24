<?php

namespace App\Http\Controllers\Monitoring;

use App\Enums\MonitoringStatus;
use App\Enums\WbpStatus;
use App\Http\Controllers\Controller;
use App\Models\MonitoringLog;
use App\Models\ServiceCategory;
use App\Models\Wbp;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KesehatanController extends Controller
{
    public function index(Request $request): View
    {
        $category = ServiceCategory::where('slug', 'pemeriksaan-kesehatan')->firstOrFail();
        $tanggal = $request->date('tanggal')?->toDateString() ?? today()->toDateString();

        $blok = $request->string('blok')->trim()->value();
        $q = $request->string('q')->trim()->value();

        $wbps = Wbp::where('status', WbpStatus::Aktif->value)
            ->orderBy('nama')
            ->get(['id', 'nama', 'no_register', 'blok_kamar']);

        $logs = MonitoringLog::where('category_id', $category->id)
            ->whereIn('wbp_id', $wbps->pluck('id'))
            ->whereDate('tanggal', $tanggal)
            ->get(['id', 'wbp_id', 'category_id', 'status', 'tanggal'])
            ->keyBy('wbp_id');

        $statusOptions = MonitoringStatus::optionsFor($category->slug);

        $payload = $wbps->map(function (Wbp $wbp) use ($logs) {
            $log = $logs->get($wbp->id);

            return [
                'id' => $wbp->id,
                'nama' => $wbp->nama,
                'no_register' => $wbp->no_register,
                'blok_kamar' => $wbp->blok_kamar,
                'status' => $log?->status->value ?? null,
                'status_label' => $log?->status->labelKesehatan() ?? null,
                'fulfilled' => $log !== null && $log->status !== MonitoringStatus::Belum,
            ];
        })->values();

        $blokList = $wbps->pluck('blok_kamar')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $sudah = $logs->filter(fn (MonitoringLog $log) => $log->status !== MonitoringStatus::Belum)->count();
        $total = $wbps->count();
        $belum = $total - $sudah;
        $persen = $total > 0 ? (int) round(($sudah / $total) * 100) : 0;

        return view('monitoring.pemeriksaan-kesehatan', compact('category', 'statusOptions', 'sudah', 'belum', 'persen', 'payload', 'blokList', 'tanggal', 'blok', 'q'));
    }
}
