@props([
    'routes' => [],
    'id' => null,
    'icon' => null,
    'title' => '',
])

@php
    // Determine if any child route is active
    $isActive = false;
    
    // Support single route string or array of routes
    $patterns = is_array($routes) ? $routes : [$routes];
    
    foreach ($patterns as $pattern) {
        $pattern = trim($pattern);
        if (request()->routeIs($pattern)) {
            $isActive = true;
            break;
        }
    }
    
    $dropdownId = $id ?? 'navbar-' . Str::slug($title);
@endphp

<li class="nav-item dropdown {{ $isActive ? 'active' : '' }}">
    <a class="nav-link dropdown-toggle {{ $isActive ? 'active' : '' }}" 
       href="#{{ $dropdownId }}" 
       data-bs-toggle="dropdown" 
       data-bs-auto-close="outside" 
       role="button" 
       aria-expanded="{{ $isActive ? 'true' : 'false' }}">
        @if($icon)
            <span class="nav-link-icon d-md-none d-lg-inline-block">
                {{ $icon }}
            </span>
        @endif
        <span class="nav-link-title">{{ $title }}</span>
    </a>
    <div class="dropdown-menu {{ $isActive ? 'show' : '' }}">
        {{ $slot }}
    </div>
</li>
