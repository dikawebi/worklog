<x-app-layout>
    <x-slot name="header">
        <div>
            <div>
                <p class="app-eyebrow">Worklog</p>
                <h1 class="mt-1 text-xl font-bold tracking-tight text-slate-900">Catat pekerjaan harian</h1>
                <p class="mt-1 text-sm text-slate-500">Tulis apa yang selesai. Detail lain bersifat opsional.</p>
            </div>
        </div>
    </x-slot>

    <livewire:worklog.today :date="$selectedDate->toDateString()" :task-id="$prefillTaskId" />
</x-app-layout>
