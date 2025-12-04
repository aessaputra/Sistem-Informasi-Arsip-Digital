<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-brand navbar-brand-autodark">
            <a href="{{ route('dashboard') }}" class="navbar-brand">
                <img src="{{ asset('img/Diskominfo_logo.png') }}" alt="Diskominfo" style="height: 50px; width: auto;">
            </a>
        </div>
        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path d="M5 12l-2 0l9 -9l9 9l-2 0"></path>
                                <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"></path>
                                <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"></path>
                            </svg>
                        </span>
                        <span class="nav-link-title">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('surat-masuk.*') ? 'active' : '' }}" href="{{ route('surat-masuk.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z"></path>
                                <path d="M3 7l9 6l9 -6"></path>
                            </svg>
                        </span>
                        <span class="nav-link-title">Surat Masuk</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('surat-keluar.*') ? 'active' : '' }}" href="{{ route('surat-keluar.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path d="M12 19l7 -7l-7 -7"></path>
                                <path d="M19 12H5"></path>
                                <path d="M5 12l7 7"></path>
                            </svg>
                        </span>
                        <span class="nav-link-title">Surat Keluar</span>
                    </a>
                </li>
                <li class="nav-item dropdown {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                    <a class="nav-link dropdown-toggle" href="#navbar-laporan" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="{{ request()->routeIs('laporan.*') ? 'true' : 'false' }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"></path>
                                <path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z"></path>
                                <path d="M9 12h6"></path>
                                <path d="M9 16h6"></path>
                            </svg>
                        </span>
                        <span class="nav-link-title">Laporan</span>
                    </a>
                    <div class="dropdown-menu {{ request()->routeIs('laporan.*') ? 'show' : '' }}">
                        <a class="dropdown-item {{ request()->routeIs('laporan.agenda-surat-masuk') ? 'active' : '' }}" href="{{ route('laporan.agenda-surat-masuk') }}">
                            Agenda Surat Masuk
                        </a>
                        <a class="dropdown-item {{ request()->routeIs('laporan.agenda-surat-keluar') ? 'active' : '' }}" href="{{ route('laporan.agenda-surat-keluar') }}">
                            Agenda Surat Keluar
                        </a>
                        <a class="dropdown-item {{ request()->routeIs('laporan.rekap-periode') ? 'active' : '' }}" href="{{ route('laporan.rekap-periode') }}">
                            Rekap Periode
                        </a>
                        <a class="dropdown-item {{ request()->routeIs('laporan.rekap-klasifikasi') ? 'active' : '' }}" href="{{ route('laporan.rekap-klasifikasi') }}">
                            Rekap Klasifikasi
                        </a>
                    </div>
                </li>
                @if(auth()->user()?->hasRole('admin'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('klasifikasi.*') ? 'active' : '' }}" href="{{ route('klasifikasi.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path d="M4 4h6v6H4z"></path>
                                <path d="M14 4h6v6h-6z"></path>
                                <path d="M4 14h6v6H4z"></path>
                                <path d="M14 14h6v6h-6z"></path>
                            </svg>
                        </span>
                        <span class="nav-link-title">Klasifikasi Surat</span>
                    </a>
                </li>
                @endif
                @role('admin')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                                <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"></path>
                            </svg>
                        </span>
                        <span class="nav-link-title">Manajemen User</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('log-aktivitas.index') ? 'active' : '' }}" href="{{ route('log-aktivitas.index') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path d="M12 8v5l3 3"></path>
                                <path d="M12 3a9 9 0 1 0 9 9"></path>
                            </svg>
                        </span>
                        <span class="nav-link-title">Log Aktivitas</span>
                    </a>
                </li>
                @endrole
            </ul>
            
        </div>
    </div>
</aside>
