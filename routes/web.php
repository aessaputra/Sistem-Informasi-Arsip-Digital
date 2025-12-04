<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KlasifikasiController;
use App\Http\Controllers\LogAktivitasController;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Surat Masuk & Surat Keluar - accessible by operator and admin
    Route::middleware('role:operator|admin')->group(function () {
        Route::resource('surat-masuk', SuratMasukController::class);
        Route::resource('surat-keluar', SuratKeluarController::class);

        // Laporan Routes
        Route::prefix('laporan')->name('laporan.')->group(function () {
            // Agenda Surat Masuk
            Route::get('agenda-surat-masuk', [LaporanController::class, 'agendaSuratMasuk'])->name('agenda-surat-masuk');
            Route::get('agenda-surat-masuk/export-excel', [LaporanController::class, 'exportAgendaMasukExcel'])->name('agenda-surat-masuk.excel');
            Route::get('agenda-surat-masuk/export-pdf', [LaporanController::class, 'exportAgendaMasukPdf'])->name('agenda-surat-masuk.pdf');

            // Agenda Surat Keluar
            Route::get('agenda-surat-keluar', [LaporanController::class, 'agendaSuratKeluar'])->name('agenda-surat-keluar');
            Route::get('agenda-surat-keluar/export-excel', [LaporanController::class, 'exportAgendaKeluarExcel'])->name('agenda-surat-keluar.excel');
            Route::get('agenda-surat-keluar/export-pdf', [LaporanController::class, 'exportAgendaKeluarPdf'])->name('agenda-surat-keluar.pdf');

            // Rekap Periode
            Route::get('rekap-periode', [LaporanController::class, 'rekapPeriode'])->name('rekap-periode');
            Route::get('rekap-periode/export-excel', [LaporanController::class, 'exportRekapPeriodeExcel'])->name('rekap-periode.excel');
            Route::get('rekap-periode/export-pdf', [LaporanController::class, 'exportRekapPeriodePdf'])->name('rekap-periode.pdf');

            // Rekap Klasifikasi
            Route::get('rekap-klasifikasi', [LaporanController::class, 'rekapKlasifikasi'])->name('rekap-klasifikasi');
            Route::get('rekap-klasifikasi/export-excel', [LaporanController::class, 'exportRekapKlasifikasiExcel'])->name('rekap-klasifikasi.excel');
            Route::get('rekap-klasifikasi/export-pdf', [LaporanController::class, 'exportRekapKlasifikasiPdf'])->name('rekap-klasifikasi.pdf');
        });
    });

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('klasifikasi', KlasifikasiController::class)->except(['show', 'destroy']);
        Route::get('log-aktivitas', [LogAktivitasController::class, 'index'])->name('log-aktivitas.index');
    });
});

require __DIR__.'/auth.php';

