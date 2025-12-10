<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\KlasifikasiSurat;
use App\Models\LogAktivitas;
use App\Services\DuplicateDetectionService;
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
        
        $suratMasuk = $query->paginate(15)->withQueryString();
        $klasifikasi = KlasifikasiSurat::where('is_active', true)->get();

        return view('surat-masuk.index', compact('suratMasuk', 'klasifikasi'));
    }

    public function create()
    {
        $klasifikasi = KlasifikasiSurat::where('is_active', true)->get();
        
        return view('surat-masuk.create', compact('klasifikasi'));
    }

    public function store(Request $request, DuplicateDetectionService $duplicateService)
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
            'force_save' => 'nullable|boolean', // For bypassing duplicate warnings
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            
            // Check for duplicates (skip if force_save is true)
            if (!$request->boolean('force_save')) {
                $duplicateResult = null;
                
                // Check file-based duplicates if file is uploaded
                // This checks: exact file hash match OR file size + metadata similarity
                if ($request->hasFile('file_path')) {
                    $duplicateResult = $duplicateService->checkDuplicate(
                        $request->file('file_path'),
                        'surat_masuk',
                        $validated
                    );
                } else {
                    // No file uploaded - only check for exact nomor_surat match
                    // We don't warn for similar metadata alone (too many false positives)
                    $duplicateResult = $duplicateService->checkDuplicateByMetadata(
                        'surat_masuk',
                        $validated
                    );
                }

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

            $validated['jam_input'] = now();
            $validated['petugas_input_id'] = auth()->id();
            
            if ($request->hasFile('file_path')) {
                $pathDir = 'surat-masuk/' . now()->format('Y/m');
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

            $suratMasuk = SuratMasuk::create($validated);
            
            // Log duplicate detection if applicable
            if ($request->hasFile('file_path')) {
                $duplicateResult = $duplicateService->checkDuplicate(
                    $request->file('file_path'),
                    'surat_masuk',
                    $validated
                );
                $duplicateService->logDuplicateDetection($duplicateResult, 'surat_masuk', auth()->id());
            }

            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aksi' => 'create',
                'modul' => 'surat_masuk',
                'reference_table' => 'surat_masuk',
                'reference_id' => $suratMasuk->id,
                'keterangan' => 'Menambahkan surat masuk: ' . $suratMasuk->nomor_surat . 
                              ($validated['is_duplicate'] ?? false ? ' (Duplikat - Force Save)' : ''),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            \Illuminate\Support\Facades\DB::commit();
            
            $message = 'Surat masuk berhasil ditambahkan.';
            if ($validated['is_duplicate'] ?? false) {
                $message .= ' (Disimpan sebagai duplikat)';
            }
            
            toast($message, 'success');
            return redirect()->route('surat-masuk.index');
            
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            alert()->error('Gagal', 'Terjadi kesalahan saat menyimpan surat masuk: ' . $e->getMessage());
            return back()->withInput();
        }
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

    public function update(Request $request, SuratMasuk $suratMasuk, DuplicateDetectionService $duplicateService)
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
            'file_path' => 'nullable|file|mimes:pdf,doc,docx|extensions:pdf,doc,docx|max:2048',
            'force_save' => 'nullable|boolean',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            
            // Check for duplicates (skip if force_save is true)
            if (!$request->boolean('force_save')) {
                $duplicateResult = null;
                
                // Check file-based duplicates if new file is uploaded
                if ($request->hasFile('file_path')) {
                    $duplicateResult = $duplicateService->checkDuplicate(
                        $request->file('file_path'),
                        'surat_masuk',
                        $validated
                    );
                    
                    // Exclude current document from duplicate check
                    if ($duplicateResult->isDuplicate && 
                        $duplicateResult->existingDocument->id === $suratMasuk->id) {
                        $duplicateResult = null; // Not a duplicate if it's the same document
                    }
                } else {
                    // No new file - only check for exact nomor_surat match
                    $duplicateResult = $duplicateService->checkDuplicateByMetadata(
                        'surat_masuk',
                        $validated,
                        $suratMasuk->id // Exclude current document
                    );
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

            $validated['jam_input'] = now();
            $validated['petugas_input_id'] = auth()->id();
            
            if ($request->hasFile('file_path')) {
                // Delete old file
                if ($suratMasuk->file_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($suratMasuk->file_path);
                }
                
                $pathDir = 'surat-masuk/' . now()->format('Y/m');
                $validated['file_path'] = $request->file('file_path')->store($pathDir, 'public');
                
                // Update file hash and size
                $fileHash = $duplicateService->generateFileHash($request->file('file_path'));
                $validated['file_hash'] = $fileHash;
                $validated['file_size'] = $request->file('file_path')->getSize();
            }

            $suratMasuk->update($validated);
            
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
            
            \Illuminate\Support\Facades\DB::commit();
            toast('Surat masuk berhasil diperbarui.', 'success');
            return redirect()->route('surat-masuk.index');
            
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            alert()->error('Gagal', 'Terjadi kesalahan saat memperbarui surat masuk: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function destroy(Request $request, SuratMasuk $suratMasuk)
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            $nomorSurat = $suratMasuk->nomor_surat;
            $suratMasuk->delete();
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
            \Illuminate\Support\Facades\DB::commit();
            toast('Surat masuk berhasil dihapus.', 'success');
            return redirect()->route('surat-masuk.index');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            alert()->error('Gagal', 'Terjadi kesalahan saat menghapus surat masuk.');
            return back();
        }
    }
}
