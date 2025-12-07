@extends('layouts.app')
@section('title', 'Laporan Rekap Klasifikasi')
@section('content')
    <!-- Filter Card -->
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M4 4h6v6H4z" /><path d="M14 4h6v6h-6z" />
                    <path d="M4 14h6v6H4z" /><path d="M14 14h6v6h-6z" />
                </svg>
                Filter Tahun
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.rekap-klasifikasi') }}">
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
                        <button type="button" class="btn btn-success" id="btn-export-excel" data-url="{{ route('laporan.rekap-klasifikasi.excel', ['tahun' => $tahun]) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M10 12l4 5" /><path d="M10 17l4 -5" /></svg>
                            Export Excel
                        </button>
                        <button type="button" class="btn btn-danger" id="btn-export-pdf" data-url="{{ route('laporan.rekap-klasifikasi.pdf', ['tahun' => $tahun]) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M10 13l-1 2l1 2" /><path d="M14 13l1 2l-1 2" /></svg>
                            Export PDF
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row row-deck row-cards">
        <!-- Chart Card -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Grafik Rekap Klasifikasi - {{ $tahun }}</h3>
                </div>
                <div class="card-body">
                    <div id="chart-klasifikasi" style="height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- Pie Chart Card -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Distribusi Surat per Klasifikasi</h3>
                </div>
                <div class="card-body">
                    <div id="chart-pie" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Rekap Surat Per Klasifikasi - Tahun {{ $tahun }}</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter table-striped card-table">
                <thead>
                    <tr>
                        <th class="w-1">No</th>
                        <th>Kode</th>
                        <th>Klasifikasi</th>
                        <th class="text-center">Surat Masuk</th>
                        <th class="text-center">Surat Keluar</th>
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekapData as $index => $data)
                    <tr>
                        <td class="text-secondary">{{ $index + 1 }}</td>
                        <td><code>{{ $data['kode'] }}</code></td>
                        <td>{{ $data['nama'] }}</td>
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
                    <tr class="bg-primary-lt fw-bold">
                        <td colspan="3" class="text-end">TOTAL</td>
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
    // Bar Chart
    var barOptions = {
        chart: {
            type: 'bar',
            height: 350,
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
            categories: @json(collect($rekapData)->pluck('kode')),
            labels: {
                rotate: -45,
                style: { fontSize: '11px' }
            }
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
    var barChart = new ApexCharts(document.querySelector("#chart-klasifikasi"), barOptions);
    barChart.render();

    // Pie Chart
    var pieOptions = {
        chart: {
            type: 'donut',
            height: 350
        },
        series: @json(collect($rekapData)->pluck('total')),
        labels: @json(collect($rekapData)->pluck('nama')),
        colors: ['#206bc4', '#2fb344', '#f59f00', '#d63939', '#4299e1', '#667eea', '#ed8936', '#38b2ac'],
        legend: {
            position: 'bottom'
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return Math.round(val) + "%";
            },
            style: {
                colors: ['#fff']
            },
            dropShadow: {
                enabled: true,
                top: 1,
                left: 1,
                blur: 2,
                opacity: 0.5
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '50%'
                }
            }
        }
    };
    var pieChart = new ApexCharts(document.querySelector("#chart-pie"), pieOptions);
    pieChart.render();

    // SweetAlert Excel Export
    const btnExportExcel = document.getElementById('btn-export-excel');
    if (btnExportExcel) {
        btnExportExcel.addEventListener('click', function() {
            const url = this.dataset.url;

            Swal.fire({
                title: 'Export Excel',
                text: 'Apakah Anda yakin ingin mengunduh laporan dalam format Excel?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '📥 Ya, Export Excel',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: () => {
                    return new Promise((resolve) => {
                        const iframe = document.createElement('iframe');
                        iframe.style.display = 'none';
                        iframe.src = url;
                        document.body.appendChild(iframe);
                        setTimeout(() => resolve(true), 1500);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'File Excel sedang diunduh...',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            });
        });
    }

    // SweetAlert PDF Export
    const btnExportPdf = document.getElementById('btn-export-pdf');
    if (btnExportPdf) {
        btnExportPdf.addEventListener('click', function() {
            const url = this.dataset.url;

            Swal.fire({
                title: 'Export PDF',
                text: 'Apakah Anda yakin ingin mengunduh laporan dalam format PDF?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d63939',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '📄 Ya, Export PDF',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: () => {
                    return new Promise((resolve) => {
                        const iframe = document.createElement('iframe');
                        iframe.style.display = 'none';
                        iframe.src = url;
                        document.body.appendChild(iframe);
                        setTimeout(() => resolve(true), 1500);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'File PDF sedang diunduh...',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
            });
        });
    }
});
</script>
@endpush
