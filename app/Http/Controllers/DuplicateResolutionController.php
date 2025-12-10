<?php

namespace App\Http\Controllers;

use App\Models\DuplicateDetection;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DuplicateResolutionController extends Controller
{
    /**
     * Handle duplicate resolution actions
     */
    public function resolve(Request $request)
    {
        $request->validate([
            'action' => 'required|in:replace,skip,force_save,ignore',
            'duplicate_id' => 'required|exists:duplicate_detections,id',
            'document_type' => 'required|in:surat_masuk,surat_keluar',
            'document_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            $duplicate = DuplicateDetection::findOrFail($request->duplicate_id);
            $action = $request->action;

            switch ($action) {
                case 'replace':
                    $result = $this->handleReplace($duplicate, $request);
                    break;
                
                case 'skip':
                    $result = $this->handleSkip($duplicate, $request);
                    break;
                
                case 'force_save':
                    $result = $this->handleForceSave($duplicate, $request);
                    break;
                
                case 'ignore':
                    $result = $this->handleIgnore($duplicate, $request);
                    break;
                
                default:
                    throw new \Exception('Invalid action');
            }

            // Mark duplicate as resolved
            $duplicate->markAsResolved($action, auth()->id());

            // Log the resolution
            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aksi' => 'resolve_duplicate',
                'modul' => 'duplicate_detection',
                'reference_table' => 'duplicate_detections',
                'reference_id' => $duplicate->id,
                'keterangan' => "Resolved duplicate with action: {$action}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'redirect' => $result['redirect'] ?? null,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve duplicate: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle replace action - replace the original file with new one
     */
    private function handleReplace(DuplicateDetection $duplicate, Request $request): array
    {
        $originalDocument = $this->getDocument($duplicate->original_document_type, $duplicate->original_document_id);
        
        if (!$originalDocument) {
            throw new \Exception('Original document not found');
        }

        // Delete old file if exists
        if ($originalDocument->file_path) {
            Storage::disk('public')->delete($originalDocument->file_path);
        }

        // Update with new file information from request
        $updateData = [];
        if ($request->has('new_file_path')) {
            $updateData['file_path'] = $request->new_file_path;
        }
        if ($request->has('new_file_hash')) {
            $updateData['file_hash'] = $request->new_file_hash;
        }
        if ($request->has('new_file_size')) {
            $updateData['file_size'] = $request->new_file_size;
        }

        $originalDocument->update($updateData);

        return [
            'message' => 'File berhasil diganti dengan file baru.',
            'redirect' => route($duplicate->original_document_type . '.show', $originalDocument->id),
        ];
    }

    /**
     * Handle skip action - cancel the upload/update
     */
    private function handleSkip(DuplicateDetection $duplicate, Request $request): array
    {
        // Just mark as resolved, no further action needed
        return [
            'message' => 'Upload dibatalkan karena duplikat.',
            'redirect' => route($duplicate->document_type . '.index'),
        ];
    }

    /**
     * Handle force save action - save despite duplicate warning
     */
    private function handleForceSave(DuplicateDetection $duplicate, Request $request): array
    {
        // The document should already be saved with duplicate flag
        // Just update the duplicate metadata
        $document = $this->getDocument($duplicate->document_type, $duplicate->document_id);
        
        if ($document) {
            $document->update([
                'is_duplicate' => true,
                'duplicate_metadata' => array_merge($document->duplicate_metadata ?? [], [
                    'forced_save' => true,
                    'resolution_date' => now()->toISOString(),
                    'resolved_by' => auth()->id(),
                ]),
            ]);
        }

        return [
            'message' => 'Dokumen berhasil disimpan meskipun terdeteksi duplikat.',
            'redirect' => route($duplicate->document_type . '.show', $duplicate->document_id),
        ];
    }

    /**
     * Handle ignore action - ignore the duplicate warning
     */
    private function handleIgnore(DuplicateDetection $duplicate, Request $request): array
    {
        $duplicate->markAsIgnored(auth()->id());

        return [
            'message' => 'Peringatan duplikat diabaikan.',
        ];
    }

    /**
     * Get document by type and ID
     */
    private function getDocument(string $type, int $id): ?object
    {
        return match ($type) {
            'surat_masuk' => SuratMasuk::find($id),
            'surat_keluar' => SuratKeluar::find($id),
            default => null,
        };
    }

    /**
     * Get duplicate detection statistics
     */
    public function statistics()
    {
        $stats = [
            'total_detected' => DuplicateDetection::count(),
            'pending' => DuplicateDetection::pending()->count(),
            'resolved' => DuplicateDetection::resolved()->count(),
            'high_similarity' => DuplicateDetection::highSimilarity()->count(),
            'by_method' => DuplicateDetection::select('detection_method')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('detection_method')
                ->get()
                ->pluck('count', 'detection_method'),
            'recent_duplicates' => DuplicateDetection::with(['detectedBy', 'originalDocument'])
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get duplicate detection history for a document
     */
    public function history(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:surat_masuk,surat_keluar',
            'document_id' => 'required|integer',
        ]);

        $history = DuplicateDetection::where(function ($query) use ($request) {
            $query->where('document_type', $request->document_type)
                  ->where('document_id', $request->document_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('original_document_type', $request->document_type)
                  ->where('original_document_id', $request->document_id);
        })
        ->with(['detectedBy', 'resolvedBy'])
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($history);
    }

    /**
     * Bulk resolve duplicates
     */
    public function bulkResolve(Request $request)
    {
        $request->validate([
            'duplicate_ids' => 'present|array',
            'duplicate_ids.*' => 'integer|exists:duplicate_detections,id',
            'action' => 'required|in:ignore,force_resolve',
        ]);

        // Handle empty array case
        if (empty($request->duplicate_ids)) {
            return response()->json([
                'success' => true,
                'message' => 'No duplicates to resolve.',
                'resolved_count' => 0,
            ]);
        }

        try {
            DB::beginTransaction();

            $duplicates = DuplicateDetection::whereIn('id', $request->duplicate_ids)
                ->pending()
                ->get();

            $resolved = 0;
            foreach ($duplicates as $duplicate) {
                if ($request->action === 'ignore') {
                    $duplicate->markAsIgnored(auth()->id());
                } else {
                    $duplicate->markAsResolved('bulk_resolve', auth()->id());
                }
                $resolved++;
            }

            // Log bulk action
            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aksi' => 'bulk_resolve_duplicates',
                'modul' => 'duplicate_detection',
                'reference_table' => 'duplicate_detections',
                'reference_id' => null,
                'keterangan' => "Bulk resolved {$resolved} duplicates with action: {$request->action}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully resolved {$resolved} duplicates.",
                'resolved_count' => $resolved,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk resolve duplicates: ' . $e->getMessage(),
            ], 500);
        }
    }
}