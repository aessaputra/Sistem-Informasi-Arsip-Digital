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

        KlasifikasiSurat::create($validated);

        return redirect()->route('klasifikasi.index')
            ->with('success', 'Klasifikasi surat berhasil ditambahkan.');
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

        $klasifikasi->update($validated);

        return redirect()->route('klasifikasi.index')
            ->with('success', 'Klasifikasi surat berhasil diperbarui.');
    }
}
