<?php

namespace App\Http\Controllers\Monitoring;

use App\Enums\BorrowStatus;
use App\Enums\MonitoringStatus;
use App\Enums\WbpStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBorrowBookRequest;
use App\Models\BorrowBook;
use App\Models\MonitoringLog;
use App\Models\ServiceCategory;
use App\Models\Wbp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PeminjamanBukuController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();

        $borrows = BorrowBook::with('wbp')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('judul_buku', 'like', "%{$search}%")
                    ->orWhereHas('wbp', fn ($query) => $query->where('nama', 'like', "%{$search}%"));
            }))
            ->orderByDesc('tanggal_pinjam')
            ->paginate(12)
            ->withQueryString();

        $totalKoleksi = BorrowBook::distinct('judul_buku')->count('judul_buku');
        $dipinjam = BorrowBook::where('status', BorrowStatus::Dipinjam->value)->count();
        $overdue = BorrowBook::overdue()->count();
        $tersedia = max(0, $totalKoleksi - $dipinjam);
        $dipinjamHariIni = BorrowBook::whereDate('tanggal_pinjam', today())->count();

        $wbps = Wbp::where('status', WbpStatus::Aktif->value)->orderBy('nama')->get(['id', 'nama', 'no_register']);

        return view('monitoring.buku', compact('borrows', 'search', 'wbps', 'totalKoleksi', 'dipinjam', 'overdue', 'tersedia', 'dipinjamHariIni'));
    }

    public function store(StoreBorrowBookRequest $request): RedirectResponse
    {
        $borrow = BorrowBook::create($request->validated());

        MonitoringLog::create([
            'wbp_id' => $borrow->wbp_id,
            'user_id' => auth()->id(),
            'category_id' => ServiceCategory::where('slug', 'peminjaman-buku')->value('id'),
            'status' => MonitoringStatus::Selesai,
            'tanggal' => $borrow->tanggal_pinjam,
            'keterangan' => 'Meminjam buku: '.$borrow->judul_buku,
        ]);

        return back()->with('success', 'Peminjaman buku berhasil dicatat.');
    }

    public function updateStatus(Request $request, BorrowBook $borrow): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(BorrowStatus::class)],
        ]);

        $borrow->update([
            ...$data,
            'tanggal_kembali' => $data['status'] === BorrowStatus::Dikembalikan->value
                ? today()
                : $borrow->tanggal_kembali,
        ]);

        return back()->with('success', 'Status peminjaman berhasil diperbarui.');
    }
}
