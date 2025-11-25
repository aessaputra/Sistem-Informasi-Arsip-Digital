@extends('layouts.app')
@section('title', 'Detail Surat Masuk')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Detail Surat Masuk</h3>
        <div class="card-actions">
            <a href="{{ route('surat-masuk.index') }}" class="btn btn-secondary">Kembali</a>
            <a href="{{ route('surat-masuk.edit', $suratMasuk) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-2"><strong>Nomor Surat</strong><div class="text-secondary">{{ $suratMasuk->nomor_surat }}</div></div>
                <div class="mb-2"><strong>Tanggal Surat</strong><div class="text-secondary">{{ optional($suratMasuk->tanggal_surat)->format('d M Y') }}</div></div>
                <div class="mb-2"><strong>Perihal</strong><div class="text-secondary">{{ $suratMasuk->perihal }}</div></div>
                <div class="mb-2"><strong>Dari</strong><div class="text-secondary">{{ $suratMasuk->dari }}</div></div>
                <div class="mb-2"><strong>Kepada</strong><div class="text-secondary">{{ $suratMasuk->kepada }}</div></div>
                <div class="mb-2"><strong>Tanggal Surat Masuk</strong><div class="text-secondary">{{ optional($suratMasuk->tanggal_surat_masuk)->format('d M Y') }}</div></div>
            </div>
            <div class="col-md-6">
                <div class="mb-2"><strong>Klasifikasi</strong><div class="text-secondary"><span class="badge bg-blue">{{ $suratMasuk->klasifikasi->nama ?? '-' }}</span></div></div>
                <div class="mb-2"><strong>Petugas Input</strong><div class="text-secondary">{{ $suratMasuk->petugas->name ?? '-' }}</div></div>
                <div class="mb-2"><strong>Jam Input</strong><div class="text-secondary">{{ optional($suratMasuk->jam_input)->format('d M Y H:i') }}</div></div>
                <div class="mb-2"><strong>Keterangan</strong><div class="text-secondary">{{ $suratMasuk->keterangan ?? '-' }}</div></div>
                <div class="mb-2"><strong>Berkas</strong>
                    <div class="text-secondary">
                        @if($suratMasuk->file_path)
                            <a href="{{ Storage::url($suratMasuk->file_path) }}" class="btn btn-sm btn-outline" target="_blank">Unduh/Lihat</a>
                        @else
                            -
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection