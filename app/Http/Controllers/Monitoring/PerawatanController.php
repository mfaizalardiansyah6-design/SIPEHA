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

class PerawatanController extends Controller
{
    private const SLUGS = ['potong-rambut', 'potong-kuku'];

    public function index(Request $request): View
    {
        $tab = $request->string('tab')->value();

        if (! in_array($tab, self::SLUGS, true)) {
            $tab = 'potong-rambut';
        }

        $category = ServiceCategory::where('slug', $tab)->firstOrFail();

        $wbps = Wbp::where('status', WbpStatus::Aktif->value)
            ->orderBy('nama')
            ->get(['id', 'nama', 'no_register', 'blok_kamar', 'foto_url']);

        $logs = MonitoringLog::where('category_id', $category->id)
            ->whereIn('wbp_id', $wbps->pluck('id'))
            ->orderBy('tanggal')
            ->get(['id', 'wbp_id', 'category_id', 'status', 'tanggal', 'keterangan'])
            ->groupBy('wbp_id')
            ->map->last();

        $statusOptions = MonitoringStatus::optionsFor($category->slug);
        $fulfilled = $logs->filter->isTerpenuhi()->count();
        $total = $wbps->count();
        $persen = $total > 0 ? (int) round(($fulfilled / $total) * 100) : 0;

        return view('monitoring.perawatan', compact('tab', 'category', 'wbps', 'logs', 'statusOptions', 'fulfilled', 'total', 'persen'));
    }
}
