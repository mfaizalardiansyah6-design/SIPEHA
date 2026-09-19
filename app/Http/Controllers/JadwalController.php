<?php

namespace App\Http\Controllers;

use App\Models\MonitoringLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalController extends Controller
{
    public function index(Request $request): View
    {
        $tanggal = $request->date('tanggal')?->toDateString() ?? today()->toDateString();

        $todayLogs = MonitoringLog::with(['wbp', 'category'])
            ->whereHas('wbp')
            ->whereDate('tanggal', $tanggal)
            ->orderByRaw('waktu_mulai IS NULL, waktu_mulai ASC')
            ->get();

        $events = MonitoringLog::selectRaw('tanggal, count(*) as total')
            ->whereHas('wbp')
            ->whereDate('tanggal', '>=', today()->subDays(30))
            ->whereDate('tanggal', '<=', today()->addDays(30))
            ->groupBy('tanggal')
            ->get()
            ->map(fn ($event) => [
                'title' => "{$event->total} sesi",
                'start' => $event->tanggal->toDateString(),
                'allDay' => true,
            ])
            ->values()
            ->toJson();

        return view('jadwal', compact('todayLogs', 'tanggal', 'events'));
    }
}
