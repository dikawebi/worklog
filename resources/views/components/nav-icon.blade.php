@props(['name' => 'dashboard'])

<svg {{ $attributes->merge(['class' => 'h-5 w-5 shrink-0', 'fill' => 'none', 'viewBox' => '0 0 24 24', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
    @switch($name)
        @case('dashboard')
            <path d="M3 3h7v7H3zM14 3h7v4h-7zM14 12h7v9h-7zM3 14h7v7H3z" />
            @break
        @case('worklog')
            <rect x="4" y="4" width="16" height="16" rx="2" />
            <path d="M8 9h8M8 13h8M8 17h5" />
            @break
        @case('feed')
            <path d="M4 5h16M4 12h16M4 19h10" />
            @break
        @case('projects')
            <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            @break
        @case('tasks')
            <rect x="4" y="4" width="16" height="16" rx="2" />
            <path d="m8 12 2.5 2.5L16 9" />
            @break
        @case('reports')
            <path d="M4 20V10M10 20V4M16 20v-7M3 20h18" />
            @break
        @case('users')
            <circle cx="9" cy="8" r="3.2" />
            <path d="M3.5 19c.8-3 2.9-4.5 5.5-4.5s4.7 1.5 5.5 4.5" />
            <circle cx="16.5" cy="9" r="2.4" />
            <path d="M15.5 14.6c2.9.1 4.4 1.5 5 4.4" />
            @break
        @case('categories')
            <path d="M20 13 13 20l-9-9V4h7z" />
            <circle cx="7.5" cy="7.5" r="1" />
            @break
        @case('settings')
            <circle cx="12" cy="12" r="3" />
            <path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M19.1 4.9 17 7M7 17l-2.1 2.1" />
            @break
        @case('audit')
            <path d="M5 4h11l3 3v13H5z" />
            <path d="M15 4v4h4M9 12h6M9 16h4" />
            @break
        @default
            <circle cx="12" cy="12" r="8" />
    @endswitch
</svg>
