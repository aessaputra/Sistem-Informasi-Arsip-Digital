@extends('layouts.app')
@section('title', 'Log Aktivitas')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Log Aktivitas</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('log-aktivitas.index') }}" class="row g-2 align-items-end mb-3">
            <div class="col-md-3">
                <label class="form-label">User</label>
                <select name="user_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Modul</label>
                <select name="modul" class="form-select">
                    <option value="">Semua</option>
                    <option value="surat_masuk" @selected(request('modul')=='surat_masuk')>Surat Masuk</option>
                    <option value="surat_keluar" @selected(request('modul')=='surat_keluar')>Surat Keluar</option>
                    <option value="user" @selected(request('modul')=='user')>User</option>
                    <option value="klasifikasi" @selected(request('modul')=='klasifikasi')>Klasifikasi</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Aksi</label>
                <select name="aksi" class="form-select">
                    <option value="">Semua</option>
                    <option value="create" @selected(request('aksi')=='create')>Create</option>
                    <option value="update" @selected(request('aksi')=='update')>Update</option>
                    <option value="delete" @selected(request('aksi')=='delete')>Delete</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tanggal Dari</label>
                <input type="date" name="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tanggal Sampai</label>
                <input type="date" name="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Cari</button>
                <a href="{{ route('log-aktivitas.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-vcenter table-striped table-hover">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Modul</th>
                        <th>Reference ID</th>
                        <th>IP Address</th>
                        <th>User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $log->user?->name ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $log->aksi === 'create' ? 'bg-success' : ($log->aksi === 'update' ? 'bg-primary' : 'bg-danger') }}">{{ ucfirst($log->aksi) }}</span>
                            </td>
                            <td>{{ str_replace('_', ' ', $log->modul) }}</td>
                            <td>{{ $log->reference_id }}</td>
                            <td>{{ $log->ip_address }}</td>
                            <td>
                                <a class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" href="#ua-{{ $log->id }}" role="button" aria-expanded="false" aria-controls="ua-{{ $log->id }}">
                                    Lihat
                                </a>
                                <span class="text-muted ms-2">{{ \Illuminate\Support\Str::limit($log->user_agent, 40) }}</span>
                            </td>
                        </tr>
                        <tr class="collapse" id="ua-{{ $log->id }}">
                            <td colspan="7">
                                <div class="p-2">{{ $log->user_agent }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data</td>
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