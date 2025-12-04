@extends('layouts.app')
@section('title', 'Laporan Agenda Surat Masuk')
@section('content')
    <!-- Filter Card -->
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-8.5l-4.48 -4.928a2 2 0 0 1 -.52 -1.345v-2.227z" />
                </svg>
                Filter Laporan
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.agenda-surat-masuk') }}">
                <div class="row g-3">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">Tanggal Dari</label>
                        <input type="date" name="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">Tanggal Sampai</label>
                        <input type="date" name="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">Nomor Surat</label>
                        <input type="text" name="nomor_surat" class="form-control" placeholder="Cari nomor surat..." value="{{ request('nomor_surat') }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">Pengirim</label>
                        <input type="text" name="pengirim" class="form-control" placeholder="Cari pengirim..." value="{{ request('pengirim') }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">Klasifikasi</label>
                        <select name="klasifikasi_surat_id" class="form-select">
                            <option value="">Semua klasifikasi</option>
                            @foreach($klasifikasi as $k)
                                <option value="{{ $k->id }}" @selected(request('klasifikasi_surat_id')==$k->id)>{{ $k->kode }} - {{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        Filter
                    </button>
                    <a href="{{ route('laporan.agenda-surat-masuk') }}" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                        Reset
                    </a>
                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('laporan.agenda-surat-masuk.excel', request()->query()) }}" class="btn btn-success">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M10 12l4 5" /><path d="M10 17l4 -5" /></svg>
                            Export Excel
                        </a>
                        <a href="{{ route('laporan.agenda-surat-masuk.pdf', request()->query()) }}" class="btn btn-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M10 13l-1 2l1 2" /><path d="M14 13l1 2l-1 2" /></svg>
                            Export PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Agenda Surat Masuk</h3>
            <div class="ms-auto text-secondary">
                Total: <strong>{{ $suratMasuk->total() }}</strong> surat
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter table-striped table-hover card-table">
                <thead>
                    <tr>
                        <th class="w-1">No</th>
                        <th>Tanggal Surat</th>
                        <th>Nomor Surat</th>
                        <th>Perihal</th>
                        <th class="d-none d-md-table-cell">Pengirim</th>
                        <th class="d-none d-lg-table-cell">Klasifikasi</th>
                        <th class="d-none d-xl-table-cell">Tgl Diterima</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suratMasuk as $index => $surat)
                    <tr>
                        <td class="text-secondary">{{ $suratMasuk->firstItem() + $index }}</td>
                        <td class="text-nowrap">{{ $surat->tanggal_surat ? $surat->tanggal_surat->format('d/m/Y') : '-' }}</td>
                        <td>
                            <div class="text-truncate" style="max-width: 150px;" title="{{ $surat->nomor_surat }}">
                                <span class="fw-medium">{{ $surat->nomor_surat }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 250px;" title="{{ $surat->perihal }}">
                                {{ $surat->perihal }}
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <div class="text-truncate" style="max-width: 150px;" title="{{ $surat->dari }}">
                                {{ $surat->dari }}
                            </div>
                        </td>
                        <td class="d-none d-lg-table-cell">
                            <span class="badge badge-outline text-blue">{{ $surat->klasifikasi->nama ?? '-' }}</span>
                        </td>
                        <td class="d-none d-xl-table-cell text-nowrap">{{ $surat->tanggal_surat_masuk ? $surat->tanggal_surat_masuk->format('d/m/Y') : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="empty">
                                <div class="empty-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M3 7l9 6l9 -6" /></svg>
                                </div>
                                <p class="empty-title">Tidak ada data surat masuk</p>
                                <p class="empty-subtitle text-secondary">
                                    Coba ubah filter atau periode pencarian
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suratMasuk->hasPages())
        <div class="card-footer d-flex align-items-center">
            <p class="m-0 text-secondary">Menampilkan {{ $suratMasuk->firstItem() }} - {{ $suratMasuk->lastItem() }} dari {{ $suratMasuk->total() }} data</p>
            <ul class="pagination m-0 ms-auto">
                {{ $suratMasuk->links() }}
            </ul>
        </div>
        @endif
    </div>
@endsection
