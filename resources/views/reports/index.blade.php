<x-app-layout>
    <x-slot name="header">
        <div>
            <div>
                <p class="app-eyebrow">Laporan</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Laporan</h1>
                <p class="mt-1 text-sm text-slate-500">Ringkasan aktivitas worklog yang bisa diekspor.</p>
            </div>
        </div>
    </x-slot>

    <livewire:reports.index />
</x-app-layout>
