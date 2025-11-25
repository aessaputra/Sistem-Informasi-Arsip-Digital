<x-app-layout>
    <div class="row row-deck row-cards">
        <!-- Statistics Cards -->
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Total Surat Masuk</div>
                        <div class="ms-auto lh-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon text-blue" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z"></path>
                                <path d="M3 7l9 6l9 -6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="h1 mb-3">{{ $suratMasukCount ?? 0 }}</div>
                    <div class="d-flex mb-2">
                        <div>Surat masuk yang terdaftar</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Total Surat Keluar</div>
                        <div class="ms-auto lh-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon text-green" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M12 19l7 -7l-7 -7"></path>
                                <path d="M19 12H5"></path>
                                <path d="M5 12l7 7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="h1 mb-3">{{ $suratKeluarCount ?? 0 }}</div>
                    <div class="d-flex mb-2">
                        <div>Surat keluar yang terdaftar</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Log Aktivitas Hari Ini</div>
                        <div class="ms-auto lh-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon text-yellow" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M3 12h4l3 8l4 -16l3 8h4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="h1 mb-3">{{ $logAktivitasToday ?? 0 }}</div>
                    <div class="d-flex mb-2">
                        <div>Aktivitas yang tercatat hari ini</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Quick Actions</div>
                    </div>
                    <div class="d-flex flex-column gap-2 mt-3">
                        <a href="{{ route('surat-masuk.create') }}" class="btn btn-primary btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                            Tambah Surat Masuk
                        </a>
                        <a href="{{ route('surat-keluar.create') }}" class="btn btn-success btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                            Tambah Surat Keluar
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity Table -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Aktivitas Terbaru</h3>
                </div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>User</th>
                                <th>Aksi</th>
                                <th>Modul</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivities ?? [] as $activity)
                            <tr>
                                <td class="text-secondary">{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $activity->user->name ?? '-' }}</td>
                                <td><span class="badge bg-primary">{{ $activity->aksi }}</span></td>
                                <td>{{ $activity->modul }}</td>
                                <td class="text-secondary">{{ $activity->keterangan }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary">Belum ada aktivitas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
