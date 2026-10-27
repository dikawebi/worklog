<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Ruang Kerja / Notifikasi</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900">Pusat Notifikasi</h1>
                <p class="mt-1 text-sm text-slate-500">Penugasan task, mention komentar, dan kabar penting lainnya.</p>
            </div>

            <a href="{{ route('dashboard') }}" class="ui-button-secondary">Kembali ke Dasbor</a>
        </div>
    </x-slot>

    <livewire:notifications.index />
</x-app-layout>
