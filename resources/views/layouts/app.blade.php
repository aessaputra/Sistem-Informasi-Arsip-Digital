<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Sanapati Surel') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('img/sanapati_logo.png') }}">
    
    <!-- CSS files -->
    <link href="{{ asset('tabler/css/tabler.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('tabler/css/tabler-flags.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('tabler/css/tabler-payments.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('tabler/css/tabler-vendors.min.css') }}" rel="stylesheet"/>
    <!-- Custom Sidebar Active State Styles -->
    <link href="{{ asset('css/sidebar-active.css') }}" rel="stylesheet"/>
    <!-- SweetAlert Dark Mode Theme -->
    <link href="{{ asset('css/sweetalert-theme.css') }}" rel="stylesheet"/>
    <style>
        @import url('https://rsms.me/inter/inter.css');
        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }
        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }
    </style>
</head>
<body>
    <!-- Theme script (official Tabler theme handler) -->
    <script src="{{ asset('tabler/js/tabler-theme.min.js') }}"></script>
    
    <div class="page">
        <!-- Sidebar -->
        <x-layout.sidebar />
        
        <!-- Page wrapper -->
        <div class="page-wrapper">
            <!-- Page header -->
            @php
                $__title = isset($title) ? $title : (isset($attributes) ? $attributes->get('title') : trim($__env->yieldContent('title')));
                $__headerActions = isset($attributes) ? $attributes->get('headerActions') : null;
            @endphp
            <x-layout.navbar :title="$__title" :headerActions="$__headerActions" />

            <!-- Page body -->
            <div class="page-body">
                <div class="container-xl">
                    @hasSection('content')
                        @yield('content')
                    @else
                        {{ $slot }}
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabler Core -->
    <script src="{{ asset('tabler/js/tabler.min.js') }}" defer></script>
    <!-- Custom Theme Toggle -->
    <script src="{{ asset('js/theme-toggle.js') }}" defer></script>
    <!-- SweetAlert Delete Confirmation -->
    <script src="{{ asset('js/sweetalert-delete.js') }}" defer></script>
    
    <!-- SweetAlert Notifications -->
    @include('sweetalert::alert')
    
    @stack('scripts')
</body>
</html>
