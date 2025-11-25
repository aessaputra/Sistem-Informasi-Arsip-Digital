@extends('layouts.app')
@section('title', 'Edit Klasifikasi Surat')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Klasifikasi Surat</h3>
        <div class="card-actions">
            <a href="{{ route('klasifikasi.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('klasifikasi.update', $klasifikasi) }}" method="POST" class="">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Kode</label>
                    <input type="text" name="kode" class="form-control" value="{{ old('kode', $klasifikasi->kode) }}" placeholder="Kode">
                    @error('kode')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input type="text" name="nama" class="form-control" value="{{ old('nama', $klasifikasi->nama) }}" placeholder="Nama klasifikasi">
                    @error('nama')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1" @selected(old('is_active', (int) $klasifikasi->is_active)=='1')>Aktif</option>
                        <option value="0" @selected(old('is_active', (int) $klasifikasi->is_active)=='0')>Nonaktif</option>
                    </select>
                    @error('is_active')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Keterangan tambahan">{{ old('keterangan', $klasifikasi->keterangan) }}</textarea>
                    @error('keterangan')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('klasifikasi.index') }}" class="btn btn-link">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection