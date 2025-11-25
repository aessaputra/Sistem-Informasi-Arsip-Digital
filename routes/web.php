<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KlasifikasiController;
use App\Http\Controllers\LogAktivitasController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Surat Masuk & Surat Keluar - accessible by operator and admin
    Route::middleware('role:operator|admin')->group(function () {
        Route::resource('surat-masuk', SuratMasukController::class);
        Route::resource('surat-keluar', SuratKeluarController::class);
    });

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::resource('klasifikasi', KlasifikasiController::class)->except(['show', 'destroy']);
        Route::get('log-aktivitas', [LogAktivitasController::class, 'index'])->name('log-aktivitas.index');
    });
});

require __DIR__.'/auth.php';
