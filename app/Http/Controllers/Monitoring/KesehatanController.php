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

        $wbps = Wbp::where('status', WbpStatus::Aktif->value)
            ->orderBy('nama')
            ->get(['id', 'nama', 'no_register', 'blok_kamar', 'foto_url']);

        $logs = MonitoringLog::where('category_id', $category->id)
            ->whereIn('wbp_id', $wbps->pluck('id'))
            ->whereDate('tanggal', $tanggal)
            ->get(['id', 'wbp_id', 'category_id', 'status', 'tanggal'])
            ->keyBy('wbp_id');

        $statusOptions = MonitoringStatus::optionsFor($category->slug);

        $hadir = $logs->filter->isTerpenuhi()->count();
        $total = $wbps->count();
        $persen = $total > 0 ? (int) round(($hadir / $total) * 100) : 0;

        $distribusi = MonitoringLog::where('category_id', $category->id)
            ->whereDate('tanggal', $tanggal)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        return view('monitoring.pemeriksaan-kesehatan', compact('category', 'wbps', 'logs', 'statusOptions', 'hadir', 'total', 'persen', 'tanggal', 'distribusi'));
    }
}
