<?php

namespace App\Http\Controllers;

use App\Models\KlasifikasiSurat;
use Illuminate\Http\Request;

class KlasifikasiController extends Controller
{
    public function index()
    {
        $klasifikasi = KlasifikasiSurat::latest()->paginate(15);
        
        return view('klasifikasi.index', compact('klasifikasi'));
    }

    public function create()
    {
        return view('klasifikasi.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:255|unique:klasifikasi_surat',
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        try {
            KlasifikasiSurat::create($validated);
            toast('Klasifikasi surat berhasil ditambahkan.', 'success');
            return redirect()->route('klasifikasi.index');
        } catch (\Throwable $e) {
            alert()->error('Gagal', 'Terjadi kesalahan saat menyimpan klasifikasi.');
            return back()->withInput();
        }
    }

    public function edit(KlasifikasiSurat $klasifikasi)
    {
        return view('klasifikasi.edit', compact('klasifikasi'));
    }

    public function update(Request $request, KlasifikasiSurat $klasifikasi)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:255|unique:klasifikasi_surat,kode,' . $klasifikasi->id,
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $klasifikasi->update($validated);
            toast('Klasifikasi surat berhasil diperbarui.', 'success');
            return redirect()->route('klasifikasi.index');
        } catch (\Throwable $e) {
            alert()->error('Gagal', 'Terjadi kesalahan saat memperbarui klasifikasi.');
            return back()->withInput();
        }
    }

    public function destroy(KlasifikasiSurat $klasifikasi)
    {
        try {
            // Cek surat aktif (non-deleted) - hanya ini yang menghalangi penghapusan
            $activeMasuk = $klasifikasi->suratMasuk()->count();
            $activeKeluar = $klasifikasi->suratKeluar()->count();
            
            if ($activeMasuk > 0 || $activeKeluar > 0) {
                $message = "Klasifikasi tidak dapat dihapus karena masih digunakan oleh {$activeMasuk} surat masuk dan {$activeKeluar} surat keluar.";
                alert()->error('Gagal Menghapus', $message);
                return back();
            }

            // ForceDelete soft-deleted surat yang masih mereferensikan klasifikasi ini
            $trashedMasuk = $klasifikasi->suratMasuk()->onlyTrashed()->count();
            $trashedKeluar = $klasifikasi->suratKeluar()->onlyTrashed()->count();
            
            if ($trashedMasuk > 0) {
                $klasifikasi->suratMasuk()->onlyTrashed()->forceDelete();
            }
            if ($trashedKeluar > 0) {
                $klasifikasi->suratKeluar()->onlyTrashed()->forceDelete();
            }

            $klasifikasi->delete();
            
            toast('Klasifikasi surat berhasil dihapus.', 'success');
            
            return redirect()->route('klasifikasi.index');
        } catch (\Throwable $e) {
            \Log::error('Error deleting klasifikasi: ' . $e->getMessage());
            alert()->error('Gagal', 'Terjadi kesalahan: ' . $e->getMessage());
            return back();
        }
    }
}
