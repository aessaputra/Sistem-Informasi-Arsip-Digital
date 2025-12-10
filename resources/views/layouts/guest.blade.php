<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('img/sanapati_logo.png') }}">
    <!-- CSS files -->
    <link href="{{ asset('tabler/css/tabler.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('tabler/css/tabler-flags.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('tabler/css/tabler-payments.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('tabler/css/tabler-vendors.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('tabler/css/demo.min.css') }}" rel="stylesheet"/>
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
  <body  class=" d-flex flex-column">
    <script src="{{ asset('tabler/js/tabler-theme.min.js') }}"></script>
    <div class="page page-center">
      <div class="container container-tight py-4">
        @hasSection('hide_brand')
        @else
        <div class="text-center mb-4">
          <a href="." class="navbar-brand d-flex flex-column align-items-center gap-2">
            <img src="{{ asset('img/logo_banten.png') }}" alt="Logo" style="height: 100px; max-width: 220px; object-fit: contain;">
            <span class="h2 mb-0 fw-bold">{{ config('app.name') }}</span>
          </a>
        </div>
        @endif
        
        @hasSection('content')
          @yield('content')
        @else
          {{ $slot ?? '' }}
        @endif

      </div>
    </div>
    <!-- Libs JS -->
    <!-- Tabler Core -->
    <script src="{{ asset('tabler/js/tabler.min.js') }}" defer></script>
    <script src="{{ asset('tabler/js/demo.min.js') }}" defer></script>
  </body>
</html>
