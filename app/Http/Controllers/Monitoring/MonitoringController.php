<?php

namespace App\Http\Controllers\Monitoring;

use App\Enums\MonitoringStatus;
use App\Enums\WbpStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMonitoringRequest;
use App\Http\Requests\UpdateMonitoringRequest;
use App\Http\Requests\UpdateMonitoringStatusRequest;
use App\Models\MonitoringLog;
use App\Models\Wbp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MonitoringController extends Controller
{
    public function store(StoreMonitoringRequest $request): RedirectResponse
    {
        MonitoringLog::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Catatan monitoring berhasil disimpan.');
    }

    public function updateStatus(UpdateMonitoringStatusRequest $request, MonitoringLog $log): RedirectResponse
    {
        $log->update($request->validated());

        return back()->with('success', 'Status berhasil diperbarui.');
    }

    public function update(UpdateMonitoringRequest $request, MonitoringLog $log): RedirectResponse
    {
        $log->update($request->validated());

        return back()->with('success', 'Sesi berhasil diperbarui.');
    }

    /**
     * Pencarian WBP untuk form "Catat Sesi Baru" (server-side, AJAX).
     * Hanya WBP aktif yang dikembalikan, paling banyak 20 hasil.
     */
    public function searchWbp(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim()->value();
        $blok = $request->string('blok')->trim()->value();

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $wbps = Wbp::where('status', WbpStatus::Aktif->value)
            ->when($blok !== '', fn ($query) => $query->where('blok_kamar', $blok))
            ->where(function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('no_register', 'like', "%{$q}%")
                    ->orWhere('blok_kamar', 'like', "%{$q}%");
            })
            ->orderBy('nama')
            ->limit(20)
            ->get(['id', 'nama', 'no_register', 'blok_kamar'])
            ->map(fn (Wbp $wbp) => [
                'id' => $wbp->id,
                'nama' => $wbp->nama,
                'no_register' => $wbp->no_register,
                'blok_kamar' => $wbp->blok_kamar,
            ]);

        return response()->json(['results' => $wbps]);
    }

    /**
     * Simpan atau perbarui status monitoring untuk satu WBP pada satu
     * kategori di tanggal tertentu (upsert). Dipakai oleh halaman
     * perawatan, pemeriksaan kesehatan, dan layanan laundry.
     */
    public function setStatus(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'wbp_id' => ['required', 'exists:wbp,id'],
            'category_id' => ['required', 'exists:service_categories,id'],
            'status' => ['required', Rule::enum(MonitoringStatus::class)],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [
            'wbp_id.required' => 'Pilih WBP terlebih dahulu.',
            'status.required' => 'Status wajib diisi.',
        ]);

        $log = MonitoringLog::where('wbp_id', $data['wbp_id'])
            ->where('category_id', $data['category_id'])
            ->whereDate('tanggal', $data['tanggal'])
            ->first();

        if ($log) {
            $log->update([...$data, 'user_id' => auth()->id()]);
        } else {
            MonitoringLog::create([...$data, 'user_id' => auth()->id()]);
        }

        return back()->with('success', 'Status berhasil disimpan.');
    }

    public function destroy(MonitoringLog $log): RedirectResponse
    {
        $log->delete();

        return back()->with('success', 'Catatan monitoring berhasil dihapus.');
    }
}
