@extends('layouts.app')
@section('title', 'Surat Masuk')
@section('content')
    <!-- Filter Bar -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('surat-masuk.index') }}">
                <div class="row g-3">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">Nomor Surat</label>
                        <input type="text" name="nomor_surat" class="form-control" placeholder="Cari nomor surat..." value="{{ request('nomor_surat') }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">Perihal</label>
                        <input type="text" name="perihal" class="form-control" placeholder="Cari perihal..." value="{{ request('perihal') }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">Pengirim</label>
                        <input type="text" name="pengirim" class="form-control" placeholder="Cari pengirim..." value="{{ request('pengirim') }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
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
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        Filter
                    </button>
                    <a href="{{ route('surat-masuk.index') }}" class="btn btn-link">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Surat Masuk</h3>
            <div class="ms-auto text-secondary">
                Total: <strong>{{ $suratMasuk->total() }}</strong> surat
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter table-striped table-hover card-table">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ route('surat-masuk.index', array_merge(request()->all(), ['sort' => 'nomor_surat', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="text-secondary">
                                Nomor Surat
                                @if(request('sort') === 'nomor_surat')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        @if(request('direction') === 'asc')
                                            <path d="M12 5l0 14" /><path d="M18 11l-6 -6" /><path d="M6 11l6 -6" />
                                        @else
                                            <path d="M12 5l0 14" /><path d="M18 13l-6 6" /><path d="M6 13l6 6" />
                                        @endif
                                    </svg>
                                @endif
                            </a>
                        </th>
                        <th class="d-none d-sm-table-cell">
                            <a href="{{ route('surat-masuk.index', array_merge(request()->all(), ['sort' => 'tanggal_surat', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="text-secondary">
                                Tanggal
                                @if(request('sort') === 'tanggal_surat')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        @if(request('direction') === 'asc')
                                            <path d="M12 5l0 14" /><path d="M18 11l-6 -6" /><path d="M6 11l6 -6" />
                                        @else
                                            <path d="M12 5l0 14" /><path d="M18 13l-6 6" /><path d="M6 13l6 6" />
                                        @endif
                                    </svg>
                                @endif
                            </a>
                        </th>
                        <th>Perihal</th>
                        <th class="d-none d-md-table-cell">Dari</th>
                        <th class="d-none d-lg-table-cell">Klasifikasi</th>
                        <th class="d-none d-xl-table-cell">Petugas Input</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suratMasuk as $surat)
                    <tr class="cursor-pointer" onclick="window.location='{{ route('surat-masuk.show', $surat) }}'" title="Klik untuk melihat detail">
                        <td>
                            <div class="text-truncate" style="max-width: 150px;" title="{{ $surat->nomor_surat }}">
                                <span class="text-secondary fw-bold">{{ $surat->nomor_surat }}</span>
                            </div>
                        </td>
                        <td class="text-secondary d-none d-sm-table-cell">
                            <span class="text-nowrap">{{ \Carbon\Carbon::parse($surat->tanggal_surat)->format('d M Y') }}</span>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 250px;" title="{{ $surat->perihal }}">
                                <div>{{ $surat->perihal }}</div>
                                <div class="text-muted small mt-1">
                                    <span class="d-sm-none">{{ \Carbon\Carbon::parse($surat->tanggal_surat)->format('d M Y') }}</span>
                                    <span class="d-md-none">{{ $surat->dari ? ' • ' . $surat->dari : '' }}</span>
                                    <span class="d-lg-none d-block"><span class="badge badge-outline text-blue fw-medium mt-1">{{ $surat->klasifikasi->nama ?? '-' }}</span></span>
                                </div>
                            </div>
                        </td>
                        <td class="text-secondary d-none d-md-table-cell">
                            <div class="text-truncate" style="max-width: 120px;" title="{{ $surat->dari }}">
                                {{ $surat->dari }}
                            </div>
                        </td>
                        <td class="d-none d-lg-table-cell"><span class="badge badge-outline text-blue fw-medium">{{ $surat->klasifikasi->nama ?? '-' }}</span></td>
                        <td class="text-secondary d-none d-xl-table-cell">
                            <div class="text-truncate" style="max-width: 120px;" title="{{ $surat->petugas->name ?? '-' }}">
                                {{ $surat->petugas->name ?? '-' }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="empty">
                                <div class="empty-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M3 7l9 6l9 -6" />
                                    </svg>
                                </div>
                                <p class="empty-title">Belum ada data surat masuk</p>
                                <p class="empty-subtitle text-secondary">
                                    Tambahkan surat masuk baru dengan klik tombol di atas
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suratMasuk->hasPages())
        <div class="card-footer">
            <div class="row g-2 justify-content-center justify-content-sm-between">
                <div class="col-auto d-flex align-items-center">
                    <p class="m-0 text-secondary">
                        Menampilkan <strong>{{ $suratMasuk->firstItem() }} - {{ $suratMasuk->lastItem() }}</strong> dari <strong>{{ $suratMasuk->total() }} data</strong>
                    </p>
                </div>
                <div class="col-auto">
                    {{ $suratMasuk->withQueryString()->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

