@extends('layouts.app')
@section('title', 'Klasifikasi Surat')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Klasifikasi Surat</h3>
        <div class="card-actions">
            <a href="{{ route('klasifikasi.create') }}" class="btn btn-primary">Tambah Klasifikasi</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-vcenter table-striped table-hover">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($klasifikasi as $k)
                        <tr>
                            <td>{{ $k->kode }}</td>
                            <td>{{ $k->nama }}</td>
                            <td>{{ $k->keterangan }}</td>
                            <td>
                                @if($k->is_active)
                                    <span class="badge badge-outline text-success fs-5">Aktif</span>
                                @else
                                    <span class="badge badge-outline text-danger fs-5">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list">
                                    <a href="{{ route('klasifikasi.edit', $k) }}" class="btn btn-secondary btn-sm">Edit</a>
                                    <form action="{{ route('klasifikasi.update', $k) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="kode" value="{{ $k->kode }}">
                                        <input type="hidden" name="nama" value="{{ $k->nama }}">
                                        <input type="hidden" name="keterangan" value="{{ $k->keterangan }}">
                                        <input type="hidden" name="is_active" value="{{ $k->is_active ? 0 : 1 }}">
                                        <button type="submit" class="btn btn-sm {{ $k->is_active ? 'btn-warning' : 'btn-success' }}">{{ $k->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                    @if(\Illuminate\Support\Facades\Route::has('klasifikasi.destroy'))
                                    <form action="{{ route('klasifikasi.destroy', $k) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $klasifikasi->links() }}
        </div>
    </div>
</div>
@endsection