<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\KlasifikasiSurat;
use App\Exports\AgendaSuratMasukExport;
use App\Exports\AgendaSuratKeluarExport;
use App\Exports\RekapPeriodeExport;
use App\Exports\RekapKlasifikasiExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanController extends Controller
{
    /**
     * Agenda Surat Masuk Report
     */
    public function agendaSuratMasuk(Request $request)
    {
        $query = SuratMasuk::with(['petugas', 'klasifikasi']);

        // Apply filters
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_surat', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_surat', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('nomor_surat')) {
            $query->where('nomor_surat', 'like', '%' . $request->nomor_surat . '%');
        }
        if ($request->filled('pengirim')) {
            $query->where('dari', 'like', '%' . $request->pengirim . '%');
        }
        if ($request->filled('klasifikasi_surat_id')) {
            $query->where('klasifikasi_surat_id', $request->klasifikasi_surat_id);
        }

        $suratMasuk = $query->orderBy('tanggal_surat', 'desc')->paginate(20)->withQueryString();
        $klasifikasi = KlasifikasiSurat::where('is_active', true)->get();

        return view('laporan.agenda-surat-masuk', compact('suratMasuk', 'klasifikasi'));
    }

    /**
     * Export Agenda Surat Masuk to Excel
     */
    public function exportAgendaMasukExcel(Request $request)
    {
        $filename = 'agenda_surat_masuk_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new AgendaSuratMasukExport($request), $filename);
    }

    /**
     * Export Agenda Surat Masuk to PDF
     */
    public function exportAgendaMasukPdf(Request $request)
    {
        $query = SuratMasuk::with(['petugas', 'klasifikasi']);

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_surat', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_surat', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('nomor_surat')) {
            $query->where('nomor_surat', 'like', '%' . $request->nomor_surat . '%');
        }
        if ($request->filled('pengirim')) {
            $query->where('dari', 'like', '%' . $request->pengirim . '%');
        }
        if ($request->filled('klasifikasi_surat_id')) {
            $query->where('klasifikasi_surat_id', $request->klasifikasi_surat_id);
        }

        $suratMasuk = $query->orderBy('tanggal_surat', 'desc')->get();
        $filters = $request->only(['tanggal_dari', 'tanggal_sampai', 'nomor_surat', 'pengirim', 'klasifikasi_surat_id']);

        $pdf = Pdf::loadView('laporan.exports.agenda-surat-masuk-pdf', compact('suratMasuk', 'filters'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('agenda_surat_masuk_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * Agenda Surat Keluar Report
     */
    public function agendaSuratKeluar(Request $request)
    {
        $query = SuratKeluar::with(['petugas', 'klasifikasi']);

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_surat', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_surat', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('nomor_surat')) {
            $query->where('nomor_surat', 'like', '%' . $request->nomor_surat . '%');
        }
        if ($request->filled('tujuan')) {
            $query->where('tujuan', 'like', '%' . $request->tujuan . '%');
        }
        if ($request->filled('klasifikasi_surat_id')) {
            $query->where('klasifikasi_surat_id', $request->klasifikasi_surat_id);
        }

        $suratKeluar = $query->orderBy('tanggal_surat', 'desc')->paginate(20)->withQueryString();
        $klasifikasi = KlasifikasiSurat::where('is_active', true)->get();

        return view('laporan.agenda-surat-keluar', compact('suratKeluar', 'klasifikasi'));
    }

    /**
     * Export Agenda Surat Keluar to Excel
     */
    public function exportAgendaKeluarExcel(Request $request)
    {
        $filename = 'agenda_surat_keluar_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new AgendaSuratKeluarExport($request), $filename);
    }

    /**
     * Export Agenda Surat Keluar to PDF
     */
    public function exportAgendaKeluarPdf(Request $request)
    {
        $query = SuratKeluar::with(['petugas', 'klasifikasi']);

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_surat', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_surat', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('nomor_surat')) {
            $query->where('nomor_surat', 'like', '%' . $request->nomor_surat . '%');
        }
        if ($request->filled('tujuan')) {
            $query->where('tujuan', 'like', '%' . $request->tujuan . '%');
        }
        if ($request->filled('klasifikasi_surat_id')) {
            $query->where('klasifikasi_surat_id', $request->klasifikasi_surat_id);
        }

        $suratKeluar = $query->orderBy('tanggal_surat', 'desc')->get();
        $filters = $request->only(['tanggal_dari', 'tanggal_sampai', 'nomor_surat', 'tujuan', 'klasifikasi_surat_id']);

        $pdf = Pdf::loadView('laporan.exports.agenda-surat-keluar-pdf', compact('suratKeluar', 'filters'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('agenda_surat_keluar_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * Rekap Periode Report
     */
    public function rekapPeriode(Request $request)
    {
        $tahun = $request->get('tahun', now()->year);

        // Get monthly statistics for the year
        $rekapData = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $endDate = Carbon::create($tahun, $bulan, 1)->endOfMonth();

            $suratMasukCount = SuratMasuk::whereBetween('tanggal_surat', [$startDate, $endDate])->count();
            $suratKeluarCount = SuratKeluar::whereBetween('tanggal_surat', [$startDate, $endDate])->count();

            $rekapData[] = [
                'bulan' => $startDate->translatedFormat('F'),
                'bulan_num' => $bulan,
                'surat_masuk' => $suratMasukCount,
                'surat_keluar' => $suratKeluarCount,
                'total' => $suratMasukCount + $suratKeluarCount,
            ];
        }

        // Get available years for filter
        $availableYears = $this->getAvailableYears();

        // Calculate totals
        $totalMasuk = collect($rekapData)->sum('surat_masuk');
        $totalKeluar = collect($rekapData)->sum('surat_keluar');

        return view('laporan.rekap-periode', compact('rekapData', 'tahun', 'availableYears', 'totalMasuk', 'totalKeluar'));
    }

    /**
     * Export Rekap Periode to Excel
     */
    public function exportRekapPeriodeExcel(Request $request)
    {
        $filename = 'rekap_periode_' . $request->get('tahun', now()->year) . '.xlsx';
        return Excel::download(new RekapPeriodeExport($request), $filename);
    }

    /**
     * Export Rekap Periode to PDF
     */
    public function exportRekapPeriodePdf(Request $request)
    {
        $tahun = $request->get('tahun', now()->year);

        $rekapData = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $endDate = Carbon::create($tahun, $bulan, 1)->endOfMonth();

            $suratMasukCount = SuratMasuk::whereBetween('tanggal_surat', [$startDate, $endDate])->count();
            $suratKeluarCount = SuratKeluar::whereBetween('tanggal_surat', [$startDate, $endDate])->count();

            $rekapData[] = [
                'bulan' => $startDate->translatedFormat('F'),
                'surat_masuk' => $suratMasukCount,
                'surat_keluar' => $suratKeluarCount,
                'total' => $suratMasukCount + $suratKeluarCount,
            ];
        }

        $totalMasuk = collect($rekapData)->sum('surat_masuk');
        $totalKeluar = collect($rekapData)->sum('surat_keluar');

        $pdf = Pdf::loadView('laporan.exports.rekap-periode-pdf', compact('rekapData', 'tahun', 'totalMasuk', 'totalKeluar'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('rekap_periode_' . $tahun . '.pdf');
    }

    /**
     * Rekap Klasifikasi Report
     */
    public function rekapKlasifikasi(Request $request)
    {
        $tahun = $request->get('tahun', now()->year);

        $klasifikasi = KlasifikasiSurat::where('is_active', true)->get();

        $rekapData = [];
        foreach ($klasifikasi as $k) {
            $suratMasukCount = SuratMasuk::where('klasifikasi_surat_id', $k->id)
                ->whereYear('tanggal_surat', $tahun)
                ->count();
            $suratKeluarCount = SuratKeluar::where('klasifikasi_surat_id', $k->id)
                ->whereYear('tanggal_surat', $tahun)
                ->count();

            $rekapData[] = [
                'kode' => $k->kode,
                'nama' => $k->nama,
                'surat_masuk' => $suratMasukCount,
                'surat_keluar' => $suratKeluarCount,
                'total' => $suratMasukCount + $suratKeluarCount,
            ];
        }

        $availableYears = $this->getAvailableYears();
        $totalMasuk = collect($rekapData)->sum('surat_masuk');
        $totalKeluar = collect($rekapData)->sum('surat_keluar');

        return view('laporan.rekap-klasifikasi', compact('rekapData', 'tahun', 'availableYears', 'totalMasuk', 'totalKeluar'));
    }

    /**
     * Export Rekap Klasifikasi to Excel
     */
    public function exportRekapKlasifikasiExcel(Request $request)
    {
        $filename = 'rekap_klasifikasi_' . $request->get('tahun', now()->year) . '.xlsx';
        return Excel::download(new RekapKlasifikasiExport($request), $filename);
    }

    /**
     * Export Rekap Klasifikasi to PDF
     */
    public function exportRekapKlasifikasiPdf(Request $request)
    {
        $tahun = $request->get('tahun', now()->year);

        $klasifikasi = KlasifikasiSurat::where('is_active', true)->get();

        $rekapData = [];
        foreach ($klasifikasi as $k) {
            $suratMasukCount = SuratMasuk::where('klasifikasi_surat_id', $k->id)
                ->whereYear('tanggal_surat', $tahun)
                ->count();
            $suratKeluarCount = SuratKeluar::where('klasifikasi_surat_id', $k->id)
                ->whereYear('tanggal_surat', $tahun)
                ->count();

            $rekapData[] = [
                'kode' => $k->kode,
                'nama' => $k->nama,
                'surat_masuk' => $suratMasukCount,
                'surat_keluar' => $suratKeluarCount,
                'total' => $suratMasukCount + $suratKeluarCount,
            ];
        }

        $totalMasuk = collect($rekapData)->sum('surat_masuk');
        $totalKeluar = collect($rekapData)->sum('surat_keluar');

        $pdf = Pdf::loadView('laporan.exports.rekap-klasifikasi-pdf', compact('rekapData', 'tahun', 'totalMasuk', 'totalKeluar'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('rekap_klasifikasi_' . $tahun . '.pdf');
    }

    /**
     * Get available years from database
     */
    private function getAvailableYears()
    {
        $masukYears = SuratMasuk::selectRaw('YEAR(tanggal_surat) as year')
            ->distinct()
            ->pluck('year')
            ->toArray();

        $keluarYears = SuratKeluar::selectRaw('YEAR(tanggal_surat) as year')
            ->distinct()
            ->pluck('year')
            ->toArray();

        $years = array_unique(array_merge($masukYears, $keluarYears));
        sort($years);

        if (empty($years)) {
            $years = [now()->year];
        }

        return $years;
    }
}
