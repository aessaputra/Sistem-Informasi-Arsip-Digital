@extends('layouts.app')
@section('title', 'Tambah Surat Masuk')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Surat Masuk</h3>
        <div class="card-actions">
            <a href="{{ route('surat-masuk.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <!-- Duplicate Warning Component -->
        <x-duplicate-warning 
            :existing-document="session('existing_document')"
            :similarity-score="session('similarity_score', 0)"
            :detection-method="session('detection_method', '')"
            :is-update="false"
        />

        <form action="{{ route('surat-masuk.store') }}" method="POST" enctype="multipart/form-data" class="">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" class="form-control" value="{{ old('tanggal_surat') }}">
                    @error('tanggal_surat')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nomor Surat</label>
                    <input type="text" name="nomor_surat" class="form-control" value="{{ old('nomor_surat') }}" placeholder="Nomor surat">
                    @error('nomor_surat')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Perihal</label>
                    <input type="text" name="perihal" class="form-control" value="{{ old('perihal') }}" placeholder="Perihal">
                    @error('perihal')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Dari</label>
                    <input type="text" name="dari" class="form-control" value="{{ old('dari') }}" placeholder="Pengirim">
                    @error('dari')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kepada</label>
                    <input type="text" name="kepada" class="form-control" value="{{ old('kepada') }}" placeholder="Penerima">
                    @error('kepada')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Surat Masuk</label>
                    <input type="date" name="tanggal_surat_masuk" class="form-control" value="{{ old('tanggal_surat_masuk') }}">
                    @error('tanggal_surat_masuk')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">Klasifikasi</label>
                    <select name="klasifikasi_surat_id" class="form-select">
                        <option value="">Pilih klasifikasi</option>
                        @foreach($klasifikasi as $k)
                            <option value="{{ $k->id }}" @selected(old('klasifikasi_surat_id')==$k->id)>{{ $k->kode }} - {{ $k->nama }}</option>
                        @endforeach
                    </select>
                    @error('klasifikasi_surat_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Keterangan tambahan">{{ old('keterangan') }}</textarea>
                    @error('keterangan')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">File Surat (pdf/doc/docx, maks 2MB)</label>
                    <input type="file" name="file_path" class="form-control">
                    @error('file_path')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('surat-masuk.index') }}" class="btn btn-link">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection