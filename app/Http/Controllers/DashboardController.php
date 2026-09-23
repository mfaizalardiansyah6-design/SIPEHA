<?php

namespace App\Http\Controllers;

use App\Enums\MonitoringStatus;
use App\Enums\UserRole;
use App\Enums\WbpStatus;
use App\Models\MonitoringLog;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Wbp;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('dashboard.stats', 300, fn () => $this->buildStats());

        $stats['per_category'] = collect($stats['per_category']);
        $stats['per_blok'] = collect($stats['per_blok']);
        $stats['notifications'] = collect($stats['notifications']);

        return view('dashboard', ['stats' => $stats]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStats(): array
    {
        $today = today();
        $totalWbp = Wbp::where('status', WbpStatus::Aktif->value)->count();
        $totalWbpAll = Wbp::withTrashed()->count();
        $nonaktifWbp = Wbp::where('status', WbpStatus::NonAktif->value)->count();
        $totalPetugas = User::count();
        $petugasAdmin = User::where('role', UserRole::Admin->value)->count();
        $petugasUser = User::where('role', UserRole::User->value)->count();

        $logsToday = MonitoringLog::whereDate('tanggal', $today)->get();
        $terpenuhiToday = $logsToday->filter->isTerpenuhi()->count();
        $belumToday = $logsToday
            ->filter(fn (MonitoringLog $log) => ! $log->isTerpenuhi() && $log->status !== MonitoringStatus::Dibatalkan)
            ->count();

        $terpenuhiTotal = MonitoringLog::whereIn('status', [
            MonitoringStatus::Selesai->value,
            MonitoringStatus::Hadir->value,
            MonitoringStatus::Diterima->value,
        ])->count();

        $categories = ServiceCategory::orderBy('nama_layanan')->get();

        $recentLogs = MonitoringLog::with('category')
            ->whereDate('tanggal', '>=', today()->subDays(30))
            ->orderBy('tanggal')
            ->get(['wbp_id', 'category_id', 'status', 'tanggal']);

        $latestByWbpCategory = $recentLogs
            ->groupBy(fn (MonitoringLog $log) => $log->wbp_id.'-'.$log->category_id)
            ->map->last();

        $perCategory = $categories->map(function (ServiceCategory $category) use ($latestByWbpCategory, $totalWbp) {
            $items = $latestByWbpCategory->filter(fn (MonitoringLog $log) => $log->category_id === $category->id);
            $fulfilled = $items->filter->isTerpenuhi()->count();

            return [
                'nama' => $category->nama_layanan,
                'terpenuhi' => $fulfilled,
                'total' => $totalWbp,
                'persen' => $totalWbp > 0 ? (int) round(($fulfilled / $totalWbp) * 100) : 0,
            ];
        })->values()->all();

        $perBlok = Wbp::where('status', WbpStatus::Aktif->value)
            ->selectRaw('blok_kamar, count(*) as total')
            ->groupBy('blok_kamar')
            ->orderByDesc('total')
            ->pluck('total', 'blok_kamar')
            ->all();

        $activeWbps = Wbp::where('status', WbpStatus::Aktif->value)
            ->select(['id', 'nama', 'blok_kamar', 'foto_url'])
            ->with(['monitoringLogs' => fn ($query) => $query
                ->with('category:id,nama_layanan,slug')
                ->select(['wbp_id', 'category_id', 'status', 'tanggal'])
                ->orderBy('tanggal')])
            ->get();

        $notifications = collect();
        foreach ($activeWbps as $wbp) {
            $latestByCategory = $wbp->monitoringLogs->groupBy('category_id')->map->last();
            $overdue = $latestByCategory->filter(function (MonitoringLog $log) {
                return ! $log->isTerpenuhi() && $log->tanggal->lt(today()->subDays(7));
            });

            if ($overdue->isNotEmpty()) {
                $notifications->push([
                    'wbp_id' => $wbp->id,
                    'nama' => $wbp->nama,
                    'blok_kamar' => $wbp->blok_kamar,
                    'foto_url' => $wbp->foto_url,
                    'jumlah' => $overdue->count(),
                    'hari' => (int) $overdue->map(fn (MonitoringLog $log) => today()->diffInDays($log->tanggal))->max(),
                    'kategori' => $overdue->map(fn (MonitoringLog $log) => $log->category->nama_layanan)->take(3)->join(', '),
                ]);
            }
        }

        return [
            'total_wbp' => $totalWbp,
            'total_wbp_all' => $totalWbpAll,
            'nonaktif_wbp' => $nonaktifWbp,
            'total_petugas' => $totalPetugas,
            'petugas_admin' => $petugasAdmin,
            'petugas_user' => $petugasUser,
            'terpenuhi_hari_ini' => $terpenuhiToday,
            'belum_hari_ini' => $belumToday,
            'terpenuhi_total' => $terpenuhiTotal,
            'per_category' => $perCategory,
            'per_blok' => $perBlok,
            'notifications' => $notifications->sortByDesc('hari')->take(10)->values()->all(),
        ];
    }
}
