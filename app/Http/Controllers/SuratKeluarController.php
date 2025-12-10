<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use App\Models\KlasifikasiSurat;
use App\Models\LogAktivitas;
use App\Services\DuplicateDetectionService;
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

    public function store(Request $request, DuplicateDetectionService $duplicateService)
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
            'force_save' => 'nullable|boolean',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            
            // Check for duplicates (skip if force_save is true)
            // Note: nomor_surat already has unique validation in Laravel, but we add
            // duplicate detection for file-based checks and better UX
            if (!$request->boolean('force_save')) {
                $duplicateResult = null;
                
                // Check file-based duplicates if file is uploaded
                // This checks: exact file hash match OR file size + metadata similarity
                if ($request->hasFile('file_path')) {
                    $duplicateResult = $duplicateService->checkDuplicate(
                        $request->file('file_path'),
                        'surat_keluar',
                        $validated
                    );
                    
                    if ($duplicateResult && $duplicateResult->isDuplicate) {
                        \Illuminate\Support\Facades\DB::rollBack();
                        
                        return back()->withInput()->with([
                            'duplicate_detected' => true,
                            'duplicate_data' => $duplicateResult->toArray(),
                            'existing_document' => $duplicateResult->existingDocument,
                            'similarity_score' => $duplicateResult->similarityScore,
                            'detection_method' => $duplicateResult->detectionMethod,
                        ]);
                    }
                }
                // No metadata-only check needed for surat_keluar store
                // because nomor_surat already has unique validation rule
            }

            $validated['jam_input'] = now();
            $validated['petugas_input_id'] = auth()->id();
            
            if ($request->hasFile('file_path')) {
                $pathDir = 'surat-keluar/' . now()->format('Y/m');
                $validated['file_path'] = $request->file('file_path')->store($pathDir, 'public');
                
                // Generate and store file hash for future duplicate detection
                $fileHash = $duplicateService->generateFileHash($request->file('file_path'));
                $validated['file_hash'] = $fileHash;
                $validated['file_size'] = $request->file('file_path')->getSize();
            }

            // Mark as duplicate if force saving
            if ($request->boolean('force_save') && $request->has('is_duplicate_override')) {
                $validated['is_duplicate'] = true;
                $validated['duplicate_metadata'] = [
                    'forced_save' => true,
                    'override_reason' => 'User chose to save despite duplicate warning',
                    'detected_at' => now()->toISOString(),
                ];
            }

            $suratKeluar = SuratKeluar::create($validated);
            
            // Log duplicate detection if applicable
            if ($request->hasFile('file_path')) {
                $duplicateResult = $duplicateService->checkDuplicate(
                    $request->file('file_path'),
                    'surat_keluar',
                    $validated
                );
                $duplicateService->logDuplicateDetection($duplicateResult, 'surat_keluar', auth()->id());
            }

            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aksi' => 'create',
                'modul' => 'surat_keluar',
                'reference_table' => 'surat_keluar',
                'reference_id' => $suratKeluar->id,
                'keterangan' => 'Menambahkan surat keluar: ' . $suratKeluar->nomor_surat . 
                              ($validated['is_duplicate'] ?? false ? ' (Duplikat - Force Save)' : ''),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            \Illuminate\Support\Facades\DB::commit();
            
            $message = 'Surat keluar berhasil ditambahkan.';
            if ($validated['is_duplicate'] ?? false) {
                $message .= ' (Disimpan sebagai duplikat)';
            }
            
            toast($message, 'success');
            return redirect()->route('surat-keluar.index');
            
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            alert()->error('Gagal', 'Terjadi kesalahan saat menyimpan surat keluar: ' . $e->getMessage());
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

    public function update(Request $request, SuratKeluar $suratKeluar, DuplicateDetectionService $duplicateService)
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
            'force_save' => 'nullable|boolean',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            
            // Check for duplicates (skip if force_save is true)
            // Note: nomor_surat already has unique validation (ignoring self)
            if (!$request->boolean('force_save')) {
                $duplicateResult = null;
                
                // Check file-based duplicates if new file is uploaded
                if ($request->hasFile('file_path')) {
                    $duplicateResult = $duplicateService->checkDuplicate(
                        $request->file('file_path'),
                        'surat_keluar',
                        $validated
                    );
                    
                    // Exclude current document from duplicate check
                    if ($duplicateResult->isDuplicate && 
                        $duplicateResult->existingDocument->id === $suratKeluar->id) {
                        $duplicateResult = null; // Not a duplicate if it's the same document
                    }
                    
                    if ($duplicateResult && $duplicateResult->isDuplicate) {
                        \Illuminate\Support\Facades\DB::rollBack();
                        
                        return back()->withInput()->with([
                            'duplicate_detected' => true,
                            'duplicate_data' => $duplicateResult->toArray(),
                            'existing_document' => $duplicateResult->existingDocument,
                            'similarity_score' => $duplicateResult->similarityScore,
                            'detection_method' => $duplicateResult->detectionMethod,
                            'is_update' => true,
                        ]);
                    }
                }
                // No metadata-only check needed for surat_keluar update
                // because nomor_surat already has unique validation rule
            }

            $validated['jam_input'] = now();
            $validated['petugas_input_id'] = auth()->id();
            
            if ($request->hasFile('file_path')) {
                // Delete old file
                if ($suratKeluar->file_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($suratKeluar->file_path);
                }
                
                $pathDir = 'surat-keluar/' . now()->format('Y/m');
                $validated['file_path'] = $request->file('file_path')->store($pathDir, 'public');
                
                // Update file hash and size
                $fileHash = $duplicateService->generateFileHash($request->file('file_path'));
                $validated['file_hash'] = $fileHash;
                $validated['file_size'] = $request->file('file_path')->getSize();
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
            alert()->error('Gagal', 'Terjadi kesalahan saat memperbarui surat keluar: ' . $e->getMessage());
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
