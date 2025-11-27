@extends('layouts.app')
@section('title', 'Log Aktivitas')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Log Aktivitas</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('log-aktivitas.index') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-select">
                        <option value="">Semua</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label">Modul</label>
                    <select name="modul" class="form-select">
                        <option value="">Semua</option>
                        <option value="surat_masuk" @selected(request('modul')=='surat_masuk')>Surat Masuk</option>
                        <option value="surat_keluar" @selected(request('modul')=='surat_keluar')>Surat Keluar</option>
                        <option value="user" @selected(request('modul')=='user')>User</option>
                        <option value="klasifikasi" @selected(request('modul')=='klasifikasi')>Klasifikasi</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-2">
                    <label class="form-label">Aksi</label>
                    <select name="aksi" class="form-select">
                        <option value="">Semua</option>
                        <option value="create" @selected(request('aksi')=='create')>Create</option>
                        <option value="update" @selected(request('aksi')=='update')>Update</option>
                        <option value="delete" @selected(request('aksi')=='delete')>Delete</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-2">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" name="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
                </div>
                <div class="col-12 col-sm-6 col-lg-2">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" name="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Cari</button>
                <a href="{{ route('log-aktivitas.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-vcenter table-striped table-hover card-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th class="d-none d-md-table-cell">User</th>
                        <th>Aksi</th>
                        <th class="d-none d-lg-table-cell">Modul</th>
                        <th class="d-none d-xl-table-cell">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <div class="text-truncate" style="max-width: 150px;">
                                    <div class="fw-bold">{{ $log->created_at->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $log->created_at->format('H:i') }}</small>
                                    <div class="d-md-none text-muted small mt-1">
                                        <div>{{ $log->user?->name ?? '-' }}</div>
                                        <div class="d-lg-none">{{ str_replace('_', ' ', ucfirst($log->modul)) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <div class="text-truncate" style="max-width: 120px;" title="{{ $log->user?->name ?? '-' }}">
                                    {{ $log->user?->name ?? '-' }}
                                </div>
                            </td>
                            <td>
                                @if($log->aksi === 'create')
                                    <span class="badge badge-outline text-success">Create</span>
                                @elseif($log->aksi === 'update')
                                    <span class="badge badge-outline text-primary">Update</span>
                                @else
                                    <span class="badge badge-outline text-danger">Delete</span>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell">
                                <span class="badge badge-outline text-secondary">{{ str_replace('_', ' ', ucfirst($log->modul)) }}</span>
                            </td>
                            <td class="d-none d-xl-table-cell">
                                <code class="text-muted">{{ $log->ip_address }}</code>
                            </td>
                        </tr>
                    @empty
                        <tr>
                        <td colspan="5" class="text-center py-5">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection