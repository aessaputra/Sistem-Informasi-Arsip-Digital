@extends('layouts.app')
@section('title', 'Laporan Rekap Periode')
@section('content')
    <!-- Filter Card -->
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                    <path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" />
                    <path d="M8 15h2v2h-2z" />
                </svg>
                Filter Periode
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.rekap-periode') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">Tahun</label>
                        <select name="tahun" class="form-select">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" @selected($tahun == $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                            Tampilkan
                        </button>
                    </div>
                    <div class="col-12 col-lg-6 ms-auto text-end">
                        <a href="{{ route('laporan.rekap-periode.excel', ['tahun' => $tahun]) }}" class="btn btn-success">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M10 12l4 5" /><path d="M10 17l4 -5" /></svg>
                            Export Excel
                        </a>
                        <a href="{{ route('laporan.rekap-periode.pdf', ['tahun' => $tahun]) }}" class="btn btn-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M10 13l-1 2l1 2" /><path d="M14 13l1 2l-1 2" /></svg>
                            Export PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-deck row-cards mb-3">
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Total Surat Masuk</div>
                    </div>
                    <div class="h1 mb-0 mt-2">{{ number_format($totalMasuk) }}</div>
                    <div class="text-muted">Tahun {{ $tahun }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Total Surat Keluar</div>
                    </div>
                    <div class="h1 mb-0 mt-2">{{ number_format($totalKeluar) }}</div>
                    <div class="text-muted">Tahun {{ $tahun }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Total Keseluruhan</div>
                    </div>
                    <div class="h1 mb-0 mt-2">{{ number_format($totalMasuk + $totalKeluar) }}</div>
                    <div class="text-muted">Tahun {{ $tahun }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Card -->
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Grafik Rekap Per Bulan - Tahun {{ $tahun }}</h3>
        </div>
        <div class="card-body">
            <div id="chart-periode" style="height: 300px;"></div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Rekap Surat Per Bulan - Tahun {{ $tahun }}</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter table-striped card-table">
                <thead>
                    <tr>
                        <th class="w-1">No</th>
                        <th>Bulan</th>
                        <th class="text-center">Surat Masuk</th>
                        <th class="text-center">Surat Keluar</th>
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekapData as $data)
                    <tr>
                        <td class="text-secondary">{{ $data['bulan_num'] }}</td>
                        <td>{{ $data['bulan'] }}</td>
                        <td class="text-center">
                            <span class="badge bg-blue-lt">{{ $data['surat_masuk'] }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-green-lt">{{ $data['surat_keluar'] }}</span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold">{{ $data['total'] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-light fw-bold">
                        <td colspan="2" class="text-end">TOTAL</td>
                        <td class="text-center">{{ $totalMasuk }}</td>
                        <td class="text-center">{{ $totalKeluar }}</td>
                        <td class="text-center">{{ $totalMasuk + $totalKeluar }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var options = {
        chart: {
            type: 'bar',
            height: 300,
            toolbar: { show: false }
        },
        series: [{
            name: 'Surat Masuk',
            data: @json(collect($rekapData)->pluck('surat_masuk'))
        }, {
            name: 'Surat Keluar',
            data: @json(collect($rekapData)->pluck('surat_keluar'))
        }],
        xaxis: {
            categories: @json(collect($rekapData)->pluck('bulan'))
        },
        colors: ['#206bc4', '#2fb344'],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '60%',
            },
        },
        dataLabels: { enabled: false },
        legend: {
            position: 'top',
            horizontalAlign: 'right'
        }
    };
    var chart = new ApexCharts(document.querySelector("#chart-periode"), options);
    chart.render();
});
</script>
@endpush
