<?php

namespace App\Exports;

use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\KlasifikasiSurat;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapKlasifikasiExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $request;
    protected $tahun;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->tahun = $request->get('tahun', now()->year);
    }

    public function array(): array
    {
        $klasifikasi = KlasifikasiSurat::where('is_active', true)->get();
        $data = [];
        $no = 0;
        $totalMasuk = 0;
        $totalKeluar = 0;

        foreach ($klasifikasi as $k) {
            $no++;
            $suratMasukCount = SuratMasuk::where('klasifikasi_surat_id', $k->id)
                ->whereYear('tanggal_surat', $this->tahun)
                ->count();
            $suratKeluarCount = SuratKeluar::where('klasifikasi_surat_id', $k->id)
                ->whereYear('tanggal_surat', $this->tahun)
                ->count();

            $totalMasuk += $suratMasukCount;
            $totalKeluar += $suratKeluarCount;

            $data[] = [
                $no,
                $k->kode,
                $k->nama,
                $suratMasukCount,
                $suratKeluarCount,
                $suratMasukCount + $suratKeluarCount,
            ];
        }

        // Add total row
        $data[] = [
            '',
            '',
            'TOTAL',
            $totalMasuk,
            $totalKeluar,
            $totalMasuk + $totalKeluar,
        ];

        return $data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode',
            'Klasifikasi',
            'Surat Masuk',
            'Surat Keluar',
            'Total',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $klasifikasiCount = KlasifikasiSurat::where('is_active', true)->count();
        $lastRow = $klasifikasiCount + 2; // header + data rows + total

        return [
            1 => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'ED8936'],
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
            $lastRow => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ];
    }
}
