@extends('layouts.app')
@section('title', 'Detail Surat Keluar')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Detail Surat Keluar</h3>
        <div class="card-actions">
            <div class="btn-list">
                <a href="{{ route('surat-keluar.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
                <a href="{{ route('surat-keluar.edit', $suratKeluar) }}" class="btn btn-sm btn-primary">Edit</a>
                <form action="{{ route('surat-keluar.destroy', $suratKeluar) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus surat ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="mb-2"><strong>Nomor Surat</strong><div class="text-secondary">{{ $suratKeluar->nomor_surat }}</div></div>
                <div class="mb-2"><strong>Tanggal Surat</strong><div class="text-secondary">{{ optional($suratKeluar->tanggal_surat)->format('d M Y') }}</div></div>
                <div class="mb-2"><strong>Perihal</strong><div class="text-secondary">{{ $suratKeluar->perihal }}</div></div>
                <div class="mb-2"><strong>Tujuan</strong><div class="text-secondary">{{ $suratKeluar->tujuan }}</div></div>
                <div class="mb-2"><strong>Dari</strong><div class="text-secondary">{{ $suratKeluar->dari }}</div></div>
                <div class="mb-2"><strong>Tanggal Keluar</strong><div class="text-secondary">{{ optional($suratKeluar->tanggal_keluar)->format('d M Y') }}</div></div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-2"><strong>Klasifikasi</strong><div class="text-secondary"><span class="badge badge-outline text-green fs-4">{{ $suratKeluar->klasifikasi->nama ?? '-' }}</span></div></div>
                <div class="mb-2"><strong>Petugas Input</strong><div class="text-secondary">{{ $suratKeluar->petugas->name ?? '-' }}</div></div>
                <div class="mb-2"><strong>Jam Input</strong><div class="text-secondary">{{ optional($suratKeluar->jam_input)->format('d M Y H:i') }}</div></div>
                <div class="mb-2"><strong>Keterangan</strong><div class="text-secondary">{{ $suratKeluar->keterangan ?? '-' }}</div></div>
                <div class="mb-2"><strong>Berkas</strong>
                    <div class="text-secondary">
                        @if($suratKeluar->file_path)
                            <a href="{{ Storage::url($suratKeluar->file_path) }}" class="btn btn-sm btn-outline" target="_blank">Unduh/Lihat</a>
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