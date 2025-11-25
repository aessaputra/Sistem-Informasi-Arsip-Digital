<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\KlasifikasiSurat;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class SuratMasukController extends Controller
{
    public function index(Request $request)
    {
        $query = SuratMasuk::with(['petugas', 'klasifikasi']);
        
        // Apply filters
        if ($request->filled('nomor_surat')) {
            $query->where('nomor_surat', 'like', '%' . $request->nomor_surat . '%');
        }
        
        if ($request->filled('perihal')) {
            $query->where('perihal', 'like', '%' . $request->perihal . '%');
        }
        
        if ($request->filled('pengirim')) {
            $query->where('dari', 'like', '%' . $request->pengirim . '%');
        }
        
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_surat', $request->tanggal);
        }
        
        // Apply sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortField, ['nomor_surat', 'tanggal_surat'])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }
        
        $suratMasuk = $query->paginate(15)->withQueryString();

        return view('surat-masuk.index', compact('suratMasuk'));
    }

    public function create()
    {
        $klasifikasi = KlasifikasiSurat::where('is_active', true)->get();
        
        return view('surat-masuk.create', compact('klasifikasi'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_surat' => 'required|date',
            'nomor_surat' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'dari' => 'required|string|max:255',
            'kepada' => 'required|string|max:255',
            'tanggal_surat_masuk' => 'required|date',
            'klasifikasi_surat_id' => 'required|exists:klasifikasi_surat,id',
            'keterangan' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $validated['jam_input'] = now();
        $validated['petugas_input_id'] = auth()->id();

        if ($request->hasFile('file_path')) {
            $validated['file_path'] = $request->file('file_path')->store('surat-masuk', 'public');
        }

        $suratMasuk = SuratMasuk::create($validated);

        // Log activity
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aksi' => 'create',
            'modul' => 'surat_masuk',
            'reference_table' => 'surat_masuk',
            'reference_id' => $suratMasuk->id,
            'keterangan' => 'Menambahkan surat masuk: ' . $suratMasuk->nomor_surat,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('surat-masuk.index')
            ->with('success', 'Surat masuk berhasil ditambahkan.');
    }

    public function show(SuratMasuk $suratMasuk)
    {
        $suratMasuk->load(['petugas', 'klasifikasi', 'lampiran']);
        
        return view('surat-masuk.show', compact('suratMasuk'));
    }

    public function edit(SuratMasuk $suratMasuk)
    {
        $klasifikasi = KlasifikasiSurat::where('is_active', true)->get();
        
        return view('surat-masuk.edit', compact('suratMasuk', 'klasifikasi'));
    }

    public function update(Request $request, SuratMasuk $suratMasuk)
    {
        $validated = $request->validate([
            'tanggal_surat' => 'required|date',
            'nomor_surat' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'dari' => 'required|string|max:255',
            'kepada' => 'required|string|max:255',
            'tanggal_surat_masuk' => 'required|date',
            'klasifikasi_surat_id' => 'required|exists:klasifikasi_surat,id',
            'keterangan' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($request->hasFile('file_path')) {
            $validated['file_path'] = $request->file('file_path')->store('surat-masuk', 'public');
        }

        $suratMasuk->update($validated);

        // Log activity
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aksi' => 'update',
            'modul' => 'surat_masuk',
            'reference_table' => 'surat_masuk',
            'reference_id' => $suratMasuk->id,
            'keterangan' => 'Mengubah surat masuk: ' . $suratMasuk->nomor_surat,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('surat-masuk.index')
            ->with('success', 'Surat masuk berhasil diperbarui.');
    }

    public function destroy(Request $request, SuratMasuk $suratMasuk)
    {
        $nomorSurat = $suratMasuk->nomor_surat;
        
        $suratMasuk->delete();

        // Log activity
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aksi' => 'delete',
            'modul' => 'surat_masuk',
            'reference_table' => 'surat_masuk',
            'reference_id' => $suratMasuk->id,
            'keterangan' => 'Menghapus surat masuk: ' . $nomorSurat,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('surat-masuk.index')
            ->with('success', 'Surat masuk berhasil dihapus.');
    }
}
