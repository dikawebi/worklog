@php($user = auth()->user())

<nav class="flex flex-1 flex-col px-4 pb-4" aria-label="Menu utama">
    <div class="flex h-16 shrink-0 items-center gap-2">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <img
                src="{{ asset('images/logo-bp.svg') }}"
                alt="PT Borneo Prima"
                class="block h-9 w-9 object-contain drop-shadow-[0_6px_14px_rgb(0_102_255_/_0.2)]"
            >
            <span class="flex flex-col gap-0.5">
                <span class="text-xs font-extrabold tracking-[0.02em] text-white">PT BORNEO PRIMA</span>
                <span class="text-[9px] font-bold uppercase tracking-[0.18em] text-blue-300">IT Worklog</span>
            </span>
        </a>
    </div>

    <div class="mt-4 flex-1">
        <ul class="space-y-1">
                <li>
                    <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="dashboard">
                        Dasbor
                    </x-sidebar-link>
                </li>
                <li>
                    <x-sidebar-link :href="route('worklogs.index')" :active="request()->routeIs('worklogs.*')" icon="worklog">
                        Worklog
                    </x-sidebar-link>
                </li>
                <li>
                    <x-sidebar-link :href="route('feed')" :active="request()->routeIs('feed')" icon="feed">
                        Aktivitas Tim
                    </x-sidebar-link>
                </li>
                <li>
                    <x-sidebar-link :href="route('projects.index')" :active="request()->routeIs('projects.*')" icon="projects">
                        Projects
                    </x-sidebar-link>
                </li>
                <li>
                    <x-sidebar-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')" icon="tasks">
                        Tugas
                    </x-sidebar-link>
                </li>
                <li>
                    <x-sidebar-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" icon="reports">
                        Laporan
                    </x-sidebar-link>
                </li>
        </ul>

        @if ($user?->isAdmin())
            <section class="mt-6 border-t border-slate-800 pt-5">
                <p class="app-sidebar-label mb-2 px-3">Admin</p>
                <ul class="space-y-1">
                    <li>
                        <x-sidebar-link :href="route('admin.users')" :active="request()->routeIs('admin.users')" icon="users">
                            Users
                        </x-sidebar-link>
                    </li>
                    <li>
                        <x-sidebar-link :href="route('admin.categories')" :active="request()->routeIs('admin.categories')" icon="categories">
                            Kategori
                        </x-sidebar-link>
                    </li>
                    <li>
                        <x-sidebar-link :href="route('admin.settings')" :active="request()->routeIs('admin.settings')" icon="settings">
                            Pengaturan
                        </x-sidebar-link>
                    </li>
                    <li>
                        <x-sidebar-link :href="route('admin.audit')" :active="request()->routeIs('admin.audit')" icon="audit">
                            Audit Log
                        </x-sidebar-link>
                    </li>
                    <li>
                        <x-sidebar-link :href="route('admin.trash')" :active="request()->routeIs('admin.trash')" icon="audit">
                            Pemulihan Data
                        </x-sidebar-link>
                    </li>
                </ul>
            </section>
        @endif
    </div>

    <p class="mt-auto px-3 pt-6 text-[11px] leading-4 text-slate-400">
        v1.1 MVP · Tampilan menu mengikuti hak akses.
    </p>
</nav>
