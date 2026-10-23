<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">IT Worklog</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900">{{ $title }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
            </div>

            <a href="{{ route('dashboard') }}" class="ui-button-secondary">
                Kembali ke Dasbor
            </a>
        </div>
    </x-slot>

    <div class="ui-card mx-auto max-w-3xl p-8 text-center sm:p-12">
        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-soft text-brand">
            <x-nav-icon :name="$icon" class="h-8 w-8" />
        </span>

        <p class="mt-5 text-xs font-bold uppercase tracking-[0.16em] text-brand">Modul dalam pengembangan</p>
        <h2 class="mt-2 text-xl font-extrabold text-slate-900">{{ $title }}</h2>
        <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">
            Fondasi aplikasi, autentikasi, role, dan skema database sudah siap. Halaman ini akan diisi pada sprint berikutnya sesuai backlog.
        </p>

        <div class="mt-6 flex flex-wrap items-center justify-center gap-2 text-xs font-semibold text-slate-500">
            <span class="ui-badge bg-slate-100 text-slate-600">Laravel 13</span>
            <span class="ui-badge bg-slate-100 text-slate-600">Blade + Livewire</span>
            <span class="ui-badge bg-slate-100 text-slate-600">PT Borneo Prima</span>
        </div>
    </div>
</x-app-layout>
