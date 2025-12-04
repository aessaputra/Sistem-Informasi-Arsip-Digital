<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Periode</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
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
            font-size: 18px;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0 0;
            font-size: 14px;
            font-weight: normal;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 11px;
            color: #666;
        }
        .meta-info {
            margin-bottom: 15px;
            font-size: 10px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th,
        table.data-table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
        }
        table.data-table th {
            background-color: #667eea;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        table.data-table td {
            text-align: center;
        }
        table.data-table td:nth-child(2) {
            text-align: left;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        table.data-table tfoot tr {
            background-color: #e2e8f0 !important;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            font-size: 9px;
            text-align: right;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Rekap Periode</h1>
        <h2>Tahun {{ $tahun }}</h2>
        <p>Dinas Komunikasi dan Informatika</p>
    </div>

    <div class="meta-info">
        <strong>Dicetak:</strong> {{ now()->format('d/m/Y H:i') }}
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="10%">No</th>
                <th width="30%">Bulan</th>
                <th width="20%">Surat Masuk</th>
                <th width="20%">Surat Keluar</th>
                <th width="20%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapData as $index => $data)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $data['bulan'] }}</td>
                <td>{{ $data['surat_masuk'] }}</td>
                <td>{{ $data['surat_keluar'] }}</td>
                <td>{{ $data['total'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">TOTAL</td>
                <td>{{ $totalMasuk }}</td>
                <td>{{ $totalKeluar }}</td>
                <td>{{ $totalMasuk + $totalKeluar }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dicetak oleh: {{ auth()->user()->name ?? 'System' }} | {{ now()->format('d F Y H:i:s') }}
    </div>
</body>
</html>
