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

class LaundryController extends Controller
{
    public function index(Request $request): View
    {
        $category = ServiceCategory::where('slug', 'layanan-laundry')->firstOrFail();
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
                'fulfilled' => $log instanceof MonitoringLog && $log->isTerpenuhi(),
            ];
        })->values();

        $blokList = $wbps->pluck('blok_kamar')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('monitoring.laundry', compact('category', 'statusOptions', 'payload', 'blokList', 'tanggal', 'blok', 'q'));
    }
}
