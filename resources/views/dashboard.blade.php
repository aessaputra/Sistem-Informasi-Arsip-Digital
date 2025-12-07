@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
    $todayMasuk = \App\Models\SuratMasuk::whereDate('tanggal_surat_masuk', today())->count();
    $todayKeluar = \App\Models\SuratKeluar::whereDate('tanggal_keluar', today())->count();
    $monthMasuk = \App\Models\SuratMasuk::whereMonth('tanggal_surat_masuk', now()->month)->whereYear('tanggal_surat_masuk', now()->year)->count();
    $monthKeluar = \App\Models\SuratKeluar::whereMonth('tanggal_keluar', now()->month)->whereYear('tanggal_keluar', now()->year)->count();
    $recentMasuk = \App\Models\SuratMasuk::with('klasifikasi')->latest()->limit(5)->get();
    $recentKeluar = \App\Models\SuratKeluar::with('klasifikasi')->latest()->limit(5)->get();
    $labels = [];
    $seriesMasuk = [];
    $seriesKeluar = [];
    for ($i = 11; $i >= 0; $i--) {
        $d = now()->subMonths($i);
        $labels[] = $d->format('M Y');
        $seriesMasuk[] = \App\Models\SuratMasuk::whereMonth('tanggal_surat_masuk', $d->month)->whereYear('tanggal_surat_masuk', $d->year)->count();
        $seriesKeluar[] = \App\Models\SuratKeluar::whereMonth('tanggal_keluar', $d->month)->whereYear('tanggal_keluar', $d->year)->count();
    }
    $totalUserAktif = \App\Models\User::where('is_active', true)->count();
@endphp

<div class="row row-cards mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-evenly gap-2">
                    <a href="{{ route('surat-masuk.create') }}" class="btn btn-success btn-lg flex-fill">Tambah Surat Masuk</a>
                    <a href="{{ route('surat-keluar.create') }}" class="btn btn-warning btn-lg flex-fill">Tambah Surat Keluar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards">
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="text-secondary">Surat Masuk Hari Ini</div>
                <div class="h1">{{ $todayMasuk }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="text-secondary">Surat Keluar Hari Ini</div>
                <div class="h1">{{ $todayKeluar }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="text-secondary">Surat Masuk Bulan Ini</div>
                <div class="h1">{{ $monthMasuk }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="text-secondary">Surat Keluar Bulan Ini</div>
                <div class="h1">{{ $monthKeluar }}</div>
            </div>
        </div>
    </div>
    @role('admin')
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="text-secondary">Total User Aktif</div>
                <div class="h1">{{ $totalUserAktif }}</div>
            </div>
        </div>
    </div>
    @endrole
</div>

<div class="row row-cards mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tren Surat Masuk/Keluar (12 Bulan)</h3>
            </div>
            <div class="card-body">
                <div id="chart-trend" style="height: 300px"></div>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards mt-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">5 Surat Masuk Terbaru</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter table-striped">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nomor</th>
                            <th>Perihal</th>
                            <th>Klasifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentMasuk as $s)
                            <tr>
                                <td>{{ optional($s->tanggal_surat_masuk)->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $s->nomor_surat }}</td>
                                <td>{{ $s->perihal }}</td>
                                <td><span class="badge badge-outline text-blue fw-medium">{{ $s->klasifikasi->nama ?? '-' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">5 Surat Keluar Terbaru</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter table-striped">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nomor</th>
                            <th>Perihal</th>
                            <th>Klasifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentKeluar as $s)
                            <tr>
                                <td>{{ optional($s->tanggal_keluar)->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $s->nomor_surat }}</td>
                                <td>{{ $s->perihal }}</td>
                                <td><span class="badge badge-outline text-green fw-medium">{{ $s->klasifikasi->nama ?? '-' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@role('admin')
<div class="row row-cards mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">10 Log Aktivitas Terbaru</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter table-striped">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>User</th>
                            <th>Aksi</th>
                            <th>Modul</th>
                            <th>Reference ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($recentActivities ?? []) as $log)
                            <tr>
                                <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $log->user?->name ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-outline {{ $log->aksi === 'create' ? 'text-success' : ($log->aksi === 'update' ? 'text-primary' : 'text-danger') }}">{{ ucfirst($log->aksi) }}</span>
                                </td>
                                <td>{{ str_replace('_', ' ', $log->modul) }}</td>
                                <td>{{ $log->reference_id }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endrole

 

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (window.ApexCharts) {
    var options = {
      chart: { type: 'area', height: 300, animations: { enabled: false } },
      series: [
        { name: 'Surat Masuk', data: @json($seriesMasuk) },
        { name: 'Surat Keluar', data: @json($seriesKeluar) }
      ],
      xaxis: { categories: @json($labels) },
      dataLabels: { enabled: false },
      stroke: { width: 2, curve: 'smooth' },
      grid: { strokeDashArray: 4 },
      colors: ['#206bc4', '#45a164']
    };
    new ApexCharts(document.querySelector('#chart-trend'), options).render();
  }
});
</script>
@endsection
