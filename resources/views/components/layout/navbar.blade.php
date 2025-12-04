<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <!-- Page title -->
                <h2 class="page-title">
                    {{ $title ?? 'Dashboard' }}
                </h2>
            </div>
            <!-- Page title actions -->
            <div class="col-auto ms-auto d-print-none">
                <div class="d-flex align-items-center">
                    <div class="btn-list me-2">
                        <!-- Dark/Light mode toggle -->
                        <button type="button" id="theme-toggle-dark" class="btn btn-icon nav-link px-0 hide-theme-dark" title="Enable dark mode" aria-label="Enable dark mode">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"></path>
                            </svg>
                        </button>
                        <button type="button" id="theme-toggle-light" class="btn btn-icon nav-link px-0 hide-theme-light" title="Enable light mode" aria-label="Enable light mode">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                                <path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7"></path>
                            </svg>
                        </button>
                        @isset($headerActions)
                            {{ $headerActions }}
                        @endisset
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown" aria-label="Open user menu">
                            @php($avatar = Auth::user()->avatar)
                            @if($avatar)
                                <span class="avatar avatar-sm" style="background-image: url({{ asset('storage/' . $avatar) }})"></span>
                            @else
                                <span class="avatar avatar-sm">{{ Str::substr(Auth::user()->name, 0, 1) }}</span>
                            @endif
                            <div class="d-none d-xl-block ps-2">
                                <div>{{ Auth::user()->name }}</div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">Profile</a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
