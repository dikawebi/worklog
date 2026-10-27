<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Kendali / Projects</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900">Projects</h1>
                <p class="mt-1 text-sm text-slate-500">Kelola project, milestone, anggota, dan progres pekerjaan.</p>
            </div>
            @can('create', App\Models\Project::class)
                <button type="button" x-data x-on:click="$dispatch('open-project-form')" class="ui-button-primary">+ Project</button>
            @endcan
        </div>
    </x-slot>

    <livewire:projects.index />
</x-app-layout>
