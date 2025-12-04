@props([
    'route' => null,
    'href' => '#',
    'icon' => null,
])

@php
    // Determine if this nav link is active
    $isActive = false;
    
    if ($route) {
        // Support multiple route patterns (comma-separated or array)
        $patterns = is_array($route) ? $route : explode(',', $route);
        foreach ($patterns as $pattern) {
            $pattern = trim($pattern);
            if (request()->routeIs($pattern)) {
                $isActive = true;
                break;
            }
        }
    }
    
    $activeClass = $isActive ? 'active' : '';
@endphp

<li class="nav-item">
    <a class="nav-link {{ $activeClass }}" href="{{ $href }}" {{ $attributes }}>
        @if($icon)
            <span class="nav-link-icon d-md-none d-lg-inline-block">
                {{ $icon }}
            </span>
        @endif
        <span class="nav-link-title">{{ $slot }}</span>
    </a>
</li>
