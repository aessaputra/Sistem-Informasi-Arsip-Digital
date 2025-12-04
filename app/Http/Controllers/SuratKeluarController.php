<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use App\Models\KlasifikasiSurat;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class SuratKeluarController extends Controller
{
    public function index(Request $request)
    {
        $query = SuratKeluar::with(['petugas', 'klasifikasi']);
        
        // Apply filters
        if ($request->filled('nomor_surat')) {
            $query->where('nomor_surat', 'like', '%' . $request->nomor_surat . '%');
        }
        
        if ($request->filled('perihal')) {
            $query->where('perihal', 'like', '%' . $request->perihal . '%');
        }
        
        if ($request->filled('tujuan')) {
            $query->where('tujuan', 'like', '%' . $request->tujuan . '%');
        }
        
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_surat', $request->tanggal);
        }
        
        if ($request->filled('klasifikasi_surat_id')) {
            $query->where('klasifikasi_surat_id', $request->klasifikasi_surat_id);
        }
        
        // Apply sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortField, ['nomor_surat', 'tanggal_surat'])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }
        
        $suratKeluar = $query->paginate(15)->withQueryString();
        $klasifikasi = KlasifikasiSurat::where('is_active', true)->get();

        return view('surat-keluar.index', compact('suratKeluar', 'klasifikasi'));
    }

    public function create()
    {
        $klasifikasi = KlasifikasiSurat::where('is_active', true)->get();
        
        return view('surat-keluar.create', compact('klasifikasi'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_surat' => 'required|date',
            'nomor_surat' => 'required|string|max:255|unique:surat_keluar,nomor_surat',
            'perihal' => 'required|string|max:255',
            'tujuan' => 'required|string|max:255',
            'dari' => 'required|string|max:255',
            'tanggal_keluar' => 'required|date',
            'klasifikasi_surat_id' => 'required|exists:klasifikasi_surat,id',
            'keterangan' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx|extensions:pdf,doc,docx|max:2048',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            $validated['jam_input'] = now();
            $validated['petugas_input_id'] = auth()->id();
            if ($request->hasFile('file_path')) {
                $pathDir = 'surat-keluar/' . now()->format('Y/m');
                $validated['file_path'] = $request->file('file_path')->store($pathDir, 'public');
            }
            $suratKeluar = SuratKeluar::create($validated);
            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aksi' => 'create',
                'modul' => 'surat_keluar',
                'reference_table' => 'surat_keluar',
                'reference_id' => $suratKeluar->id,
                'keterangan' => 'Menambahkan surat keluar: ' . $suratKeluar->nomor_surat,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            \Illuminate\Support\Facades\DB::commit();
            toast('Surat keluar berhasil ditambahkan.', 'success');
            return redirect()->route('surat-keluar.index');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            alert()->error('Gagal', 'Terjadi kesalahan saat menyimpan surat keluar.');
            return back()->withInput();
        }
    }

    public function show(SuratKeluar $suratKeluar)
    {
        $suratKeluar->load(['petugas', 'klasifikasi', 'lampiran']);
        
        return view('surat-keluar.show', compact('suratKeluar'));
    }

    public function edit(SuratKeluar $suratKeluar)
    {
        $klasifikasi = KlasifikasiSurat::where('is_active', true)->get();
        
        return view('surat-keluar.edit', compact('suratKeluar', 'klasifikasi'));
    }

    public function update(Request $request, SuratKeluar $suratKeluar)
    {
        $validated = $request->validate([
            'tanggal_surat' => 'required|date',
            'nomor_surat' => 'required|string|max:255|unique:surat_keluar,nomor_surat,' . $suratKeluar->id,
            'perihal' => 'required|string|max:255',
            'tujuan' => 'required|string|max:255',
            'dari' => 'required|string|max:255',
            'tanggal_keluar' => 'required|date',
            'klasifikasi_surat_id' => 'required|exists:klasifikasi_surat,id',
            'keterangan' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx|extensions:pdf,doc,docx|max:2048',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            $validated['jam_input'] = now();
            $validated['petugas_input_id'] = auth()->id();
            if ($request->hasFile('file_path')) {
                if ($suratKeluar->file_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($suratKeluar->file_path);
                }
                $pathDir = 'surat-keluar/' . now()->format('Y/m');
                $validated['file_path'] = $request->file('file_path')->store($pathDir, 'public');
            }
            $suratKeluar->update($validated);
            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aksi' => 'update',
                'modul' => 'surat_keluar',
                'reference_table' => 'surat_keluar',
                'reference_id' => $suratKeluar->id,
                'keterangan' => 'Mengubah surat keluar: ' . $suratKeluar->nomor_surat,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            \Illuminate\Support\Facades\DB::commit();
            toast('Surat keluar berhasil diperbarui.', 'success');
            return redirect()->route('surat-keluar.index');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            alert()->error('Gagal', 'Terjadi kesalahan saat memperbarui surat keluar.');
            return back()->withInput();
        }
    }

    public function destroy(Request $request, SuratKeluar $suratKeluar)
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            $nomorSurat = $suratKeluar->nomor_surat;
            $suratKeluar->delete();
            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aksi' => 'delete',
                'modul' => 'surat_keluar',
                'reference_table' => 'surat_keluar',
                'reference_id' => $suratKeluar->id,
                'keterangan' => 'Menghapus surat keluar: ' . $nomorSurat,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            \Illuminate\Support\Facades\DB::commit();
            toast('Surat keluar berhasil dihapus.', 'success');
            return redirect()->route('surat-keluar.index');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            alert()->error('Gagal', 'Terjadi kesalahan saat menghapus surat keluar.');
            return back();
        }
    }
}
