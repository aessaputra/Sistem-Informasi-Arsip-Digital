<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Agenda Surat Masuk</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 11px;
            color: #666;
        }
        .meta-info {
            margin-bottom: 15px;
            font-size: 9px;
        }
        .meta-info table {
            width: 100%;
        }
        .meta-info td {
            padding: 2px 5px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th,
        table.data-table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        table.data-table th {
            background-color: #4299e1;
            color: white;
            font-weight: bold;
            font-size: 9px;
        }
        table.data-table td {
            font-size: 9px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 20px;
            font-size: 8px;
            text-align: right;
            color: #666;
        }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Agenda Surat Masuk</h1>
        <p>Dinas Komunikasi dan Informatika</p>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td width="15%"><strong>Periode:</strong></td>
                <td>
                    @if(!empty($filters['tanggal_dari']) || !empty($filters['tanggal_sampai']))
                        {{ $filters['tanggal_dari'] ?? 'Awal' }} s/d {{ $filters['tanggal_sampai'] ?? 'Sekarang' }}
                    @else
                        Semua Periode
                    @endif
                </td>
                <td width="15%"><strong>Dicetak:</strong></td>
                <td>{{ now()->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Total Data:</strong></td>
                <td>{{ $suratMasuk->count() }} surat</td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="10%">Tanggal</th>
                <th width="15%">Nomor Surat</th>
                <th width="20%">Perihal</th>
                <th width="15%">Pengirim</th>
                <th width="12%">Kepada</th>
                <th width="10%">Tgl Diterima</th>
                <th width="14%">Klasifikasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suratMasuk as $index => $surat)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $surat->tanggal_surat ? $surat->tanggal_surat->format('d/m/Y') : '-' }}</td>
                <td>{{ $surat->nomor_surat }}</td>
                <td>{{ \Illuminate\Support\Str::limit($surat->perihal, 50) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($surat->dari, 30) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($surat->kepada, 25) }}</td>
                <td>{{ $surat->tanggal_surat_masuk ? $surat->tanggal_surat_masuk->format('d/m/Y') : '-' }}</td>
                <td>{{ $surat->klasifikasi->nama ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh: {{ auth()->user()->name ?? 'System' }} | {{ now()->format('d F Y H:i:s') }}
    </div>
</body>
</html>
