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

        $payload = $wbps->map(fn (Wbp $wbp) => [
            'id' => $wbp->id,
            'nama' => $wbp->nama,
            'no_register' => $wbp->no_register,
            'blok_kamar' => $wbp->blok_kamar,
            'status' => $logs->get($wbp->id)?->status->value ?? null,
            'fulfilled' => (bool) ($logs->get($wbp->id)?->isTerpenuhi() ?? false),
            'log_date' => $logs->get($wbp->id)?->tanggal->translatedFormat('d M Y') ?? null,
        ])->values();

        $blokList = $wbps->pluck('blok_kamar')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('monitoring.perawatan', compact('tab', 'category', 'statusOptions', 'payload', 'blokList', 'tanggal', 'blok', 'q'));
    }
}
