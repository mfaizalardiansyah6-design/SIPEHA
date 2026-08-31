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

class AlatIbadahController extends Controller
{
    public function index(Request $request): View
    {
        $category = ServiceCategory::where('slug', 'alat-ibadah')->firstOrFail();

        $wbps = Wbp::where('status', WbpStatus::Aktif->value)
            ->orderBy('nama')
            ->get(['id', 'nama', 'no_register', 'blok_kamar', 'agama', 'foto_url']);

        $logs = MonitoringLog::where('category_id', $category->id)
            ->whereIn('wbp_id', $wbps->pluck('id'))
            ->orderBy('tanggal')
            ->get(['id', 'wbp_id', 'category_id', 'status', 'tanggal', 'keterangan'])
            ->groupBy('wbp_id')
            ->map->last();

        $statusOptions = MonitoringStatus::optionsFor($category->slug);
        $diterima = $logs->filter->isTerpenuhi()->count();
        $total = $wbps->count();
        $persen = $total > 0 ? (int) round(($diterima / $total) * 100) : 0;

        $agamaDist = Wbp::where('status', WbpStatus::Aktif->value)
            ->selectRaw('agama, count(*) as total')
            ->groupBy('agama')
            ->orderByDesc('total')
            ->get();

        return view('monitoring.alat-ibadah', compact('category', 'wbps', 'logs', 'statusOptions', 'diterima', 'total', 'persen', 'agamaDist'));
    }
}
