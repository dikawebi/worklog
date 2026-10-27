<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Kendali / Tugas</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900">Tugas</h1>
                <p class="mt-1 text-sm text-slate-500">Lihat dan kelola tugas yang sedang dikerjakan tim.</p>
            </div>

            <a href="{{ route('dashboard') }}" class="ui-button-secondary">Kembali ke Dasbor</a>
        </div>
    </x-slot>

    <livewire:tasks.index />
</x-app-layout>
