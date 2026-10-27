<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Kendali / Tugas / Detail</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900">{{ $task->title }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $task->project?->code ?? 'Tanpa project' }} @if ($task->milestone) · {{ $task->milestone->title }} @endif</p>
            </div>

            <a href="{{ route('tasks.index') }}" class="ui-button-secondary">Kembali ke Tugas</a>
        </div>
    </x-slot>

    <div class="grid gap-5 xl:grid-cols-3">
        <section class="ui-card p-5 sm:p-6 xl:col-span-2">
            <div class="flex flex-wrap items-center gap-2">
                <span class="ui-badge {{ $task->status->value === 'done' ? 'bg-emerald-100 text-emerald-700' : ($task->status->value === 'blocked' ? 'bg-rose-100 text-rose-700' : ($task->status->value === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600')) }}">{{ $task->status->label() }}</span>
                <span class="ui-badge {{ $task->priority->value === 'urgent' ? 'bg-rose-100 text-rose-700' : ($task->priority->value === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">{{ $task->priority->label() }}</span>
                @if ($task->due_date && $task->due_date->isPast() && $task->status->value !== 'done')
                    <span class="ui-badge bg-rose-100 text-rose-700">Terlambat</span>
                @endif
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Project</p><p class="mt-1 text-sm font-semibold text-slate-800">{{ $task->project?->name ?? '—' }}</p></div>
                <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Assignee</p><p class="mt-1 text-sm font-semibold text-slate-800">{{ $task->assignee?->name ?? 'Belum ditugaskan' }}</p></div>
                <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Deadline</p><p class="mt-1 text-sm font-semibold text-slate-800">{{ $task->due_date?->translatedFormat('d F Y') ?? 'Tanpa deadline' }}</p></div>
                <div><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Dibuat oleh</p><p class="mt-1 text-sm font-semibold text-slate-800">{{ $task->creator?->name ?? '—' }}</p></div>
            </div>

            <div class="mt-5 border-t border-slate-100 pt-5">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Deskripsi</p>
                <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $task->description ?: 'Belum ada deskripsi.' }}</p>
            </div>
        </section>

        <aside class="space-y-5">
            <section class="ui-card p-5">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Status</p>
                <p class="mt-1 text-sm font-bold text-slate-900">{{ $task->status->label() }}</p>
                <p class="mt-3 text-xs text-slate-500">Ubah status dari halaman Tugas melalui tombol workflow atau board.</p>
            </section>
        </aside>
    </div>

    <div class="mt-5">
        <livewire:comments :commentable-type="\App\Models\Task::class" :commentable-id="$task->id" />
    </div>
</x-app-layout>
