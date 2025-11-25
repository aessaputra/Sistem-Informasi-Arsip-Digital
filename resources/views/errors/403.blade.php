@extends('layouts.guest')
@section('hide_brand', true)
@section('title', 'Akses Ditolak')
@section('content')
<div class="page page-center">
  <div class="container-tight py-4">
    <div class="empty">
      @php(
        $lightPath = public_path('tabler/img/errors/403-light.png')
      )
      @php(
        $darkPath = public_path('tabler/img/errors/403-dark.png')
      )
      @if(file_exists($lightPath) && file_exists($darkPath))
        <div class="empty-img">
          <img src="{{ asset('tabler/img/errors/403-light.png') }}" height="128" class="hide-theme-dark" alt="403 forbidden">
          <img src="{{ asset('tabler/img/errors/403-dark.png') }}" height="128" class="hide-theme-light" alt="403 forbidden">
        </div>
      @else
        <div class="empty-header">403</div>
      @endif
      <p class="empty-title">Akses ditolak</p>
      <p class="empty-subtitle text-secondary">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
      <div class="empty-action">
        <a href="{{ route('dashboard') }}" class="btn btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-2">
            <path d="M5 12l14 0" />
            <path d="M5 12l6 6" />
            <path d="M5 12l6 -6" />
          </svg>
          Kembali ke Dashboard
        </a>
      </div>
    </div>
  </div>
  </div>
@endsection