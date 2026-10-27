<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Projects / {{ $project->code }}</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900">{{ $project->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">Overview project, milestone, anggota, dan aktivitas.</p>
            </div>
            <a href="{{ route('projects.index') }}" class="ui-button-secondary">Kembali ke Projects</a>
        </div>
    </x-slot>

    <livewire:projects.show :project="$project" />
</x-app-layout>
