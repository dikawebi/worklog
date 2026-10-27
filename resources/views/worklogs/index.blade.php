<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Worklog / Hari Ini</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900">Catat pekerjaan harian</h1>
                <p class="mt-1 text-sm text-slate-500">Simpan aktivitas hari ini tanpa mencatat durasi atau jam kerja.</p>
            </div>

            <a href="{{ route('dashboard') }}" class="ui-button-secondary">Kembali ke Dasbor</a>
        </div>
    </x-slot>

    <livewire:worklog.today :date="$selectedDate->toDateString()" />
</x-app-layout>
