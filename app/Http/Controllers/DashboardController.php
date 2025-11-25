<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $suratMasukCount = SuratMasuk::count();
        $suratKeluarCount = SuratKeluar::count();
        $logAktivitasToday = LogAktivitas::whereDate('created_at', today())->count();
        $recentActivities = LogAktivitas::with('user')->latest()->limit(10)->get();
        
        return view('dashboard', compact(
            'suratMasukCount',
            'suratKeluarCount',
            'logAktivitasToday',
            'recentActivities'
        ));
    }
}
