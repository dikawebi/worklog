<x-app-layout>
    <x-slot name="header">
        <div>
            <div>
                <p class="app-eyebrow">Tugas</p>
                <h1 class="mt-1 text-xl font-bold tracking-tight text-slate-900">Task board</h1>
                <p class="mt-1 text-sm text-slate-500">Pindahkan pekerjaan dari satu tahap ke tahap berikutnya.</p>
            </div>
        </div>
    </x-slot>

    <livewire:tasks.index />
</x-app-layout>
