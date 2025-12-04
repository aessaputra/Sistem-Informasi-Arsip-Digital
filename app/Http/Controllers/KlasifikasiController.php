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
            $klasifikasi->delete();
            toast('Klasifikasi surat berhasil dihapus.', 'success');
            return redirect()->route('klasifikasi.index');
        } catch (\Throwable $e) {
            alert()->error('Gagal', 'Terjadi kesalahan saat menghapus klasifikasi.');
            return back();
        }
    }
}
