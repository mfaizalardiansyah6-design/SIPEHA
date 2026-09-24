<?php

namespace App\Http\Controllers;

use App\Enums\WbpStatus;
use App\Http\Requests\StoreWbpRequest;
use App\Http\Requests\UpdateWbpRequest;
use App\Models\ServiceCategory;
use App\Models\Wbp;
use App\Services\AuditLogger;
use App\Services\ImageUrl;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class WbpController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();
        $blok = $request->string('blok')->trim()->value();
        $status = $request->string('status')->trim()->value();
        $sort = $request->string('sort')->value();
        $dir = $request->string('dir')->value();

        $query = Wbp::query()->with([
            'monitoringLogs' => fn ($query) => $query
                ->select(['wbp_id', 'category_id', 'status', 'tanggal'])
                ->orderBy('tanggal'),
        ]);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('no_register', 'like', "%{$search}%")
                    ->orWhere('nik_hash', Wbp::hashNik($search));
            });
        }

        if ($blok !== '') {
            $query->where('blok_kamar', $blok);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if (! in_array($sort, ['nama', 'no_register', 'blok_kamar', 'status', 'agama'], true)) {
            $sort = 'nama';
        }

        $wbps = $query->orderBy($sort, $dir === 'desc' ? 'desc' : 'asc')->paginate(10)->withQueryString();

        $totalCategories = ServiceCategory::count();
        $blokList = Wbp::select('blok_kamar')->distinct()->orderBy('blok_kamar')->pluck('blok_kamar');

        return view('data-wbp.index', compact('wbps', 'totalCategories', 'blokList', 'search', 'blok', 'status', 'sort', 'dir'));
    }

    public function create(): View
    {
        Gate::authorize('create', Wbp::class);

        return view('data-wbp.form', [
            'wbp' => null,
            'statuses' => WbpStatus::cases(),
            'agamaOptions' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'],
        ]);
    }

    public function store(StoreWbpRequest $request): RedirectResponse
    {
        Gate::authorize('create', Wbp::class);

        $data = $request->validated();
        $data['nik_hash'] = Wbp::hashNik($data['nik']);

        try {
            if ($request->hasFile('foto')) {
                try {
                    $data['foto_url'] = $request->file('foto')->store('wbp', 'uploads');
                } catch (\Throwable $e) {
                    Log::error('Gagal mengunggah foto WBP.', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);

                    return back()
                        ->withErrors(['foto' => 'Foto gagal diunggah. Silakan coba lagi.'])
                        ->withInput();
                }
            } else {
                $data['foto_url'] = null;
            }

            $wbp = Wbp::create($data);
        } catch (UniqueConstraintViolationException $e) {
            Log::warning('Duplicate WBP data detected.', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);

            return back()
                ->withErrors($this->duplicateErrors($data))
                ->withInput();
        } catch (QueryException $e) {
            Log::error('Gagal menyimpan data WBP.', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);

            return back()
                ->withErrors(['form' => 'Data WBP gagal disimpan. Silakan coba lagi.'])
                ->withInput();
        } catch (\Throwable $e) {
            Log::error('Kesalahan tak terduga saat menyimpan data WBP.', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);

            return back()
                ->withErrors(['form' => 'Data yang dimasukkan belum valid. Silakan periksa kembali form.'])
                ->withInput();
        }

        app(AuditLogger::class)->log('create', 'wbp', $wbp->id, null, $wbp->only([
            'nama', 'no_register', 'blok_kamar', 'no_hp', 'agama', 'jenis_kelamin', 'status',
        ]));

        return redirect()->route('wbp.index')->with('success', 'Data WBP berhasil ditambahkan.');
    }

    public function edit(Wbp $wbp): View
    {
        Gate::authorize('update', $wbp);

        return view('data-wbp.form', [
            'wbp' => $wbp,
            'statuses' => WbpStatus::cases(),
            'agamaOptions' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'],
        ]);
    }

    public function update(UpdateWbpRequest $request, Wbp $wbp): RedirectResponse
    {
        Gate::authorize('update', $wbp);

        $data = $request->validated();
        $oldFotoUrl = $wbp->foto_url;

        if ($data['nik'] !== $wbp->nik) {
            $data['nik_hash'] = Wbp::hashNik($data['nik']);
        }

        try {
            if ($request->hasFile('foto')) {
                try {
                    $data['foto_url'] = $request->file('foto')->store('wbp', 'uploads');
                } catch (\Throwable $e) {
                    Log::error('Gagal mengunggah foto WBP.', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);

                    return back()
                        ->withErrors(['foto' => 'Foto gagal diunggah. Silakan coba lagi.'])
                        ->withInput();
                }
            }

            $wbp->update($data);

            if ($request->hasFile('foto') && $oldFotoUrl) {
                ImageUrl::delete($oldFotoUrl);
            }
        } catch (UniqueConstraintViolationException $e) {
            Log::warning('Duplicate WBP data detected.', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);

            return back()
                ->withErrors($this->duplicateErrors($data, $wbp->id))
                ->withInput();
        } catch (QueryException $e) {
            Log::error('Gagal memperbarui data WBP.', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);

            return back()
                ->withErrors(['form' => 'Data WBP gagal disimpan. Silakan coba lagi.'])
                ->withInput();
        } catch (\Throwable $e) {
            Log::error('Kesalahan tak terduga saat memperbarui data WBP.', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);

            return back()
                ->withErrors(['form' => 'Data yang dimasukkan belum valid. Silakan periksa kembali form.'])
                ->withInput();
        }

        app(AuditLogger::class)->log('update', 'wbp', $wbp->id, null, $wbp->only([
            'nama', 'no_register', 'blok_kamar', 'no_hp', 'agama', 'jenis_kelamin', 'status',
        ]));

        return redirect()->route('wbp.index')->with('success', 'Data WBP berhasil diperbarui.');
    }

    public function destroy(Wbp $wbp): RedirectResponse
    {
        Gate::authorize('delete', $wbp);

        $wbp->delete();

        app(AuditLogger::class)->log('delete', 'wbp', $wbp->id);

        return redirect()->route('wbp.index')->with('success', 'Data WBP berhasil dihapus.');
    }

    public function show(Wbp $wbp): View
    {
        $wbp->load([
            'monitoringLogs' => fn ($query) => $query->with('category', 'user')->orderByDesc('tanggal'),
            'borrowBooks' => fn ($query) => $query->orderByDesc('tanggal_pinjam'),
        ]);

        $totalCategories = ServiceCategory::count();
        $progress = $wbp->progressHak($wbp->monitoringLogs, $totalCategories);

        return view('data-wbp.show', compact('wbp', 'progress', 'totalCategories'));
    }

    /**
     * Pesan error yang sesuai ketika database menolak data duplikat
     * (missal karena race condition antara dua permintaan).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function duplicateErrors(array $data, ?string $ignoreId = null): array
    {
        if (! empty($data['nik'])) {
            $query = Wbp::withTrashed()->where('nik_hash', Wbp::hashNik($data['nik']));

            if ($ignoreId !== null) {
                $query->whereKeyNot($ignoreId);
            }

            if ($query->exists()) {
                return ['nik' => 'NIK sudah terdaftar. Silakan gunakan NIK yang berbeda.'];
            }
        }

        if (! empty($data['no_register'])) {
            $query = Wbp::withTrashed()->where('no_register', $data['no_register']);

            if ($ignoreId !== null) {
                $query->whereKeyNot($ignoreId);
            }

            if ($query->exists()) {
                return ['no_register' => 'Nomor register sudah digunakan. Silakan gunakan nomor register yang berbeda.'];
            }
        }

        return ['form' => 'Data WBP tidak dapat disimpan karena terdapat data yang sudah terdaftar.'];
    }
}
