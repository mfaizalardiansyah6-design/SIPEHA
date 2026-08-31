<?php

namespace App\Http\Controllers;

use App\Models\MonitoringLog;
use App\Models\ServiceCategory;
use App\Services\ExcelExporter;
use App\Services\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $range = $this->range($request);
        $kategori = $request->string('kategori')->value();

        $perCategory = $this->aggregate($range['start'], $range['end'], $kategori);

        $summary = [
            'total' => $perCategory->sum('total'),
            'terpenuhi' => $perCategory->sum('terpenuhi'),
            'belum' => $perCategory->sum('belum'),
        ];

        $categories = ServiceCategory::orderBy('nama_layanan')->get();

        return view('laporan.index', [
            'perCategory' => $perCategory,
            'summary' => $summary,
            'categories' => $categories,
            'start' => $range['start'],
            'end' => $range['end'],
            'kategori' => $kategori,
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $range = $this->range($request);
        $kategori = $request->string('kategori')->value();

        $rows = $this->aggregate($range['start'], $range['end'], $kategori)
            ->map(fn ($row) => [
                $row['nama'],
                $row['total'],
                $row['terpenuhi'],
                $row['belum'],
                $row['persen'].'%',
            ])
            ->toArray();

        return app(ExcelExporter::class)->download(
            "laporan-{$range['start']}-{$range['end']}.xlsx",
            ['Layanan', 'Total', 'Terpenuhi', 'Belum', 'Pemenuhan'],
            $rows,
            'Laporan Monitoring',
        );
    }

    public function exportPdf(Request $request): Response
    {
        $range = $this->range($request);
        $kategori = $request->string('kategori')->value();

        $perCategory = $this->aggregate($range['start'], $range['end'], $kategori);
        $summary = [
            'total' => $perCategory->sum('total'),
            'terpenuhi' => $perCategory->sum('terpenuhi'),
            'belum' => $perCategory->sum('belum'),
        ];

        $detailLogs = MonitoringLog::with(['wbp', 'category'])
            ->between($range['start'], $range['end'])
            ->when($kategori !== '', fn ($query) => $query->where('category_id', $kategori))
            ->orderBy('tanggal')
            ->limit(500)
            ->get();

        $instansi = app(Settings::class)->get('instansi');

        $pdf = Pdf::loadView('laporan.pdf', compact('perCategory', 'summary', 'range', 'instansi', 'detailLogs'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("laporan-{$range['start']}-{$range['end']}.pdf");
    }

    /**
     * @return array{start: string, end: string}
     */
    private function range(Request $request): array
    {
        return [
            'start' => $request->date('dari')?->toDateString() ?? today()->startOfMonth()->toDateString(),
            'end' => $request->date('sampai')?->toDateString() ?? today()->toDateString(),
        ];
    }

    /**
     * @return Collection<int, array{nama: string, terpenuhi: int, belum: int, total: int, persen: int}>
     */
    private function aggregate(string $start, string $end, string $kategori): Collection
    {
        return MonitoringLog::with('category')
            ->between($start, $end)
            ->when($kategori !== '', fn ($query) => $query->where('category_id', $kategori))
            ->get()
            ->groupBy('category_id')
            ->map(function ($group) {
                $terpenuhi = $group->filter->isTerpenuhi()->count();
                $total = $group->count();

                return [
                    'nama' => $group->first()->category?->nama_layanan ?? 'Tanpa kategori',
                    'terpenuhi' => $terpenuhi,
                    'belum' => $total - $terpenuhi,
                    'total' => $total,
                    'persen' => $total > 0 ? (int) round(($terpenuhi / $total) * 100) : 0,
                ];
            })
            ->values();
    }
}
