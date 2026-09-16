<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Monitoring\KesehatanController;
use App\Http\Controllers\Monitoring\LaundryController;
use App\Http\Controllers\Monitoring\MonitoringController;
use App\Http\Controllers\Monitoring\PeminjamanBukuController;
use App\Http\Controllers\Monitoring\PerawatanController;
use App\Http\Controllers\Monitoring\VideoCallController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserSettingsController;
use App\Http\Controllers\WbpController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/data-wbp', [WbpController::class, 'index'])->name('wbp.index');
    Route::get('/data-wbp/{wbp}', [WbpController::class, 'show'])->name('wbp.show')->whereUuid('wbp');

    Route::middleware('role:admin')->group(function () {
        Route::get('/data-wbp/create', [WbpController::class, 'create'])->name('wbp.create');
        Route::post('/data-wbp', [WbpController::class, 'store'])->name('wbp.store');
        Route::get('/data-wbp/{wbp}/edit', [WbpController::class, 'edit'])->name('wbp.edit');
        Route::put('/data-wbp/{wbp}', [WbpController::class, 'update'])->name('wbp.update');
        Route::delete('/data-wbp/{wbp}', [WbpController::class, 'destroy'])->name('wbp.destroy');

        Route::resource('data-petugas', PetugasController::class)
            ->except(['show'])
            ->parameters(['data-petugas' => 'user'])
            ->names('petugas');

        Route::get('/pengaturan', [SettingController::class, 'index'])->name('pengaturan.index');
        Route::put('/pengaturan/{group}', [SettingController::class, 'update'])->name('pengaturan.update');
        Route::post('/pengaturan/reset', [SettingController::class, 'reset'])->name('pengaturan.reset');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs');
    });

    Route::get('/monitoring/video-call', [VideoCallController::class, 'index'])->name('monitoring.video-call');
    Route::get('/monitoring/video-call/export', [VideoCallController::class, 'export'])->name('monitoring.video-call.export');
    Route::get('/monitoring/perawatan', [PerawatanController::class, 'index'])->name('monitoring.perawatan');
    Route::get('/monitoring/pemeriksaan-kesehatan', [KesehatanController::class, 'index'])->name('monitoring.pemeriksaan-kesehatan');
    Route::get('/monitoring/peminjaman-buku', [PeminjamanBukuController::class, 'index'])->name('monitoring.buku');
    Route::get('/monitoring/layanan-laundry', [LaundryController::class, 'index'])->name('monitoring.laundry');

    Route::post('/monitoring', [MonitoringController::class, 'store'])->name('monitoring.store');
    Route::post('/monitoring/set-status', [MonitoringController::class, 'setStatus'])->name('monitoring.set-status');
    Route::patch('/monitoring/{log}/status', [MonitoringController::class, 'updateStatus'])->name('monitoring.update-status');
    Route::delete('/monitoring/{log}', [MonitoringController::class, 'destroy'])->name('monitoring.destroy');

    Route::post('/monitoring/buku', [PeminjamanBukuController::class, 'store'])->name('monitoring.buku.store');
    Route::patch('/monitoring/buku/{borrow}/status', [PeminjamanBukuController::class, 'updateStatus'])->name('monitoring.buku.update-status');

    Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/excel', [LaporanController::class, 'exportExcel'])->name('laporan.excel');
    Route::get('/laporan/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');

    Route::get('/pengaturan-saya', [UserSettingsController::class, 'edit'])->name('pengaturan-saya.edit');
    Route::put('/pengaturan-saya/tampilan', [UserSettingsController::class, 'updateAppearance'])->name('pengaturan-saya.update-appearance');
    Route::put('/pengaturan-saya/profil', [UserSettingsController::class, 'updateProfile'])->name('pengaturan-saya.update-profile');
    Route::put('/pengaturan-saya/foto', [UserSettingsController::class, 'updatePhoto'])->name('pengaturan-saya.update-photo');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
