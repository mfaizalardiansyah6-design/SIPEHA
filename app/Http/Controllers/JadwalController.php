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
            ->whereDate('tanggal', $tanggal)
            ->orderByRaw('waktu_mulai IS NULL, waktu_mulai ASC')
            ->get();

        $events = MonitoringLog::selectRaw('tanggal, count(*) as total')
            ->whereDate('tanggal', '>=', today()->subDays(45))
            ->whereDate('tanggal', '<=', today()->addDays(45))
            ->groupBy('tanggal')
            ->get()
            ->map(fn ($event) => [
                'title' => "{$event->total} sesi",
                'date' => $event->tanggal->toDateString(),
                'allDay' => true,
            ])
            ->values()
            ->toJson();

        return view('jadwal', compact('todayLogs', 'tanggal', 'events'));
    }
}
