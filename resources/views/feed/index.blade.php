<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Ruang kerja / Aktivitas tim</p>
            <h1 class="mt-1 text-xl font-bold text-slate-900">Aktivitas Tim</h1>
            <p class="mt-1 text-sm text-slate-500">Lihat pekerjaan harian yang dicatat seluruh anggota tim IT.</p>
        </div>
    </x-slot>

    <livewire:worklog.feed />
</x-app-layout>
