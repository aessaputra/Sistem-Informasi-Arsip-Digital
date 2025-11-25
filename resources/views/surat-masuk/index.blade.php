<x-app-layout>
    <!-- Filter Bar -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('surat-masuk.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Nomor Surat</label>
                        <input type="text" name="nomor_surat" class="form-control" placeholder="Cari nomor surat..." value="{{ request('nomor_surat') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Perihal</label>
                        <input type="text" name="perihal" class="form-control" placeholder="Cari perihal..." value="{{ request('perihal') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pengirim</label>
                        <input type="text" name="pengirim" class="form-control" placeholder="Cari pengirim..." value="{{ request('pengirim') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
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
                        <th>
                            <a href="{{ route('surat-masuk.index', array_merge(request()->all(), ['sort' => 'tanggal_surat', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="text-secondary">
                                Tanggal Surat
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
                        <th>Dari</th>
                        <th>Klasifikasi</th>
                        <th>Petugas Input</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suratMasuk as $surat)
                    <tr>
                        <td><span class="text-secondary">{{ $surat->nomor_surat }}</span></td>
                        <td class="text-secondary">
                            <span class="text-nowrap">{{ \Carbon\Carbon::parse($surat->tanggal_surat)->format('d M Y') }}</span>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 250px;" title="{{ $surat->perihal }}">
                                {{ $surat->perihal }}
                            </div>
                        </td>
                        <td class="text-secondary">{{ $surat->dari }}</td>
                        <td><span class="badge bg-blue">{{ $surat->klasifikasi->nama ?? '-' }}</span></td>
                        <td class="text-secondary">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-xs me-2 rounded" style="background-image: url({{ asset('tabler/img/avatars/avatar-placeholder.png') }})"></span>
                                {{ $surat->petugas->name ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <a href="{{ route('surat-masuk.show', $surat) }}" class="btn btn-sm btn-ghost-primary" title="View">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                                    </svg>
                                </a>
                                <a href="{{ route('surat-masuk.edit', $surat) }}" class="btn btn-sm btn-ghost-secondary" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" />
                                    </svg>
                                </a>
                                <form action="{{ route('surat-masuk.destroy', $surat) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus surat ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-ghost-danger" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                        </svg>
                                    </button>
                                </form>
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
        <div class="card-footer d-flex align-items-center">
            <p class="m-0 text-secondary">Showing {{ $suratMasuk->firstItem() }} to {{ $suratMasuk->lastItem() }} of {{ $suratMasuk->total() }} entries</p>
            <ul class="pagination m-0 ms-auto">
                {{ $suratMasuk->links() }}
            </ul>
        </div>
        @endif
    </div>
</x-app-layout>
