<?php

namespace App\Exports;

use App\Models\SuratKeluar;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AgendaSuratKeluarExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $request;
    protected int $rowNumber = 0;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Return query builder for chunked processing.
     * Laravel Excel will automatically chunk this query.
     */
    public function query(): Builder
    {
        $query = SuratKeluar::query()
            ->select([
                'id',
                'tanggal_surat',
                'nomor_surat',
                'perihal',
                'dari',
                'tujuan',
                'tanggal_keluar',
                'klasifikasi_surat_id',
                'petugas_input_id',
                'keterangan',
            ])
            ->with(['petugas:id,name', 'klasifikasi:id,nama']);

        if ($this->request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_surat', '>=', $this->request->tanggal_dari);
        }
        if ($this->request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_surat', '<=', $this->request->tanggal_sampai);
        }
        if ($this->request->filled('nomor_surat')) {
            $query->where('nomor_surat', 'like', '%' . $this->request->nomor_surat . '%');
        }
        if ($this->request->filled('tujuan')) {
            $query->where('tujuan', 'like', '%' . $this->request->tujuan . '%');
        }
        if ($this->request->filled('klasifikasi_surat_id')) {
            $query->where('klasifikasi_surat_id', $this->request->klasifikasi_surat_id);
        }

        return $query->orderBy('tanggal_surat', 'desc');
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Surat',
            'Nomor Surat',
            'Perihal',
            'Dari',
            'Tujuan',
            'Tanggal Keluar',
            'Klasifikasi',
            'Petugas Input',
            'Keterangan',
        ];
    }

    public function map($surat): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $surat->tanggal_surat ? $surat->tanggal_surat->format('d/m/Y') : '-',
            $surat->nomor_surat,
            $surat->perihal,
            $surat->dari,
            $surat->tujuan,
            $surat->tanggal_keluar ? $surat->tanggal_keluar->format('d/m/Y') : '-',
            $surat->klasifikasi->nama ?? '-',
            $surat->petugas->name ?? '-',
            $surat->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '48BB78'],
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];
    }
}
