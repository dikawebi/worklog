<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="app-eyebrow">Aktivitas tim</p>
            <h1 class="mt-1 text-xl font-bold tracking-tight text-slate-900">Timeline</h1>
            <p class="mt-1 text-sm text-slate-500">Lihat pekerjaan harian yang dicatat seluruh anggota tim IT.</p>
        </div>
    </x-slot>

    <livewire:worklog.feed />
</x-app-layout>
