@props([
    'route' => null,
    'href' => '#',
])

@php
    // Determine if this dropdown item is active
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

<a class="dropdown-item {{ $activeClass }}" href="{{ $href }}" {{ $attributes }}>
    {{ $slot }}
</a>
