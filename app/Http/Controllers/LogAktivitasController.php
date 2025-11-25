<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\Request;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $query = LogAktivitas::with('user');

        // Filter by user_id
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }

        // Filter by module
        if ($request->filled('modul')) {
            $query->where('modul', $request->modul);
        }

        // Filter by action
        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }

        $logs = $query->latest()->paginate(20);
        $users = User::orderBy('name')->get();

        return view('log-aktivitas.index', compact('logs', 'users'));
    }
}
