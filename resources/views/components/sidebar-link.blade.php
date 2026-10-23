@props(['href', 'active' => false, 'icon' => 'dashboard'])

<a
    href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    class="{{ $active ? 'app-sidebar-link app-sidebar-link-active' : 'app-sidebar-link' }}"
>
    <span class="app-sidebar-icon">
        <x-nav-icon :name="$icon" />
    </span>
    <span class="min-w-0 flex-1">{{ $slot }}</span>
</a>
