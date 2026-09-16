<?php

namespace App\Http\Controllers\Monitoring;

use App\Enums\MonitoringStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMonitoringRequest;
use App\Http\Requests\UpdateMonitoringStatusRequest;
use App\Models\MonitoringLog;
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
