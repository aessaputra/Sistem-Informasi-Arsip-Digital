<?php

namespace App\Exports;

use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapPeriodeExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
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
        $data = [];
        $totalMasuk = 0;
        $totalKeluar = 0;

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $startDate = Carbon::create($this->tahun, $bulan, 1)->startOfMonth();
            $endDate = Carbon::create($this->tahun, $bulan, 1)->endOfMonth();

            $suratMasukCount = SuratMasuk::whereBetween('tanggal_surat', [$startDate, $endDate])->count();
            $suratKeluarCount = SuratKeluar::whereBetween('tanggal_surat', [$startDate, $endDate])->count();

            $totalMasuk += $suratMasukCount;
            $totalKeluar += $suratKeluarCount;

            $data[] = [
                $bulan,
                $startDate->translatedFormat('F'),
                $suratMasukCount,
                $suratKeluarCount,
                $suratMasukCount + $suratKeluarCount,
            ];
        }

        // Add total row
        $data[] = [
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
            'Bulan',
            'Surat Masuk',
            'Surat Keluar',
            'Total',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = 14; // 12 months + header + total

        return [
            1 => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '667EEA'],
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
