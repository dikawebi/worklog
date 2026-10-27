<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Ruang Kerja / Pencarian</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900">Pencarian Global</h1>
                <p class="mt-1 text-sm text-slate-500">Cari worklog, project, task, dan anggota tim dalam satu tempat.</p>
            </div>

            <a href="{{ route('dashboard') }}" class="ui-button-secondary">Kembali ke Dasbor</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-5">
        <form method="GET" action="{{ route('search') }}" class="ui-card p-4 sm:p-5">
            <label for="global-search" class="sr-only">Kata kunci pencarian</label>
            <div class="flex flex-col gap-2 sm:flex-row">
                <input id="global-search" name="q" value="{{ $query }}" type="search" autofocus class="worklog-input flex-1" placeholder="Cari pekerjaan, project, task, atau anggota...">
                <button type="submit" class="ui-button-primary">Cari</button>
            </div>
            @if ($query !== '' && mb_strlen($query) < 2)
                <p class="mt-2 text-xs font-semibold text-amber-700">Masukkan minimal 2 karakter.</p>
            @endif
        </form>

        @if ($query !== '')
            <p class="text-sm text-slate-500">{{ $total }} hasil untuk <strong class="text-slate-900">“{{ $query }}”</strong>.</p>
        @endif

        @if ($results['worklogs']->isNotEmpty())
            <section class="ui-card overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Worklog</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Pekerjaan yang cocok</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ($results['worklogs'] as $entry)
                        <a href="{{ $entry->dailyReport ? route('worklogs.show', $entry->dailyReport) : '#' }}" class="block px-5 py-3 hover:bg-slate-50">
                            <p class="text-sm font-semibold text-slate-900">{{ $entry->description }}</p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $entry->dailyReport?->user?->name }} · {{ $entry->dailyReport?->report_date?->translatedFormat('d M Y') }}
                                @if ($entry->project) · {{ $entry->project->code }} @endif
                            </p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="grid gap-5 lg:grid-cols-2">
            @if ($results['projects']->isNotEmpty())
                <section class="ui-card overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Project</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Project yang cocok</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach ($results['projects'] as $project)
                            <a href="{{ route('projects.show', $project) }}" class="block px-5 py-3 hover:bg-slate-50">
                                <p class="text-sm font-bold text-slate-900">{{ $project->code }} — {{ $project->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $project->status->label() }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($results['tasks']->isNotEmpty())
                <section class="ui-card overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Task</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Task yang cocok</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach ($results['tasks'] as $task)
                            <a href="{{ route('tasks.index') }}" class="block px-5 py-3 hover:bg-slate-50">
                                <p class="text-sm font-bold text-slate-900">{{ $task->title }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $task->project ? $task->project->code.' — '.$task->project->name : 'Tanpa project' }} · {{ $task->status->label() }} · {{ $task->assignee?->name ?? 'Belum ditugaskan' }}
                                </p>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($results['users']->isNotEmpty())
                <section class="ui-card overflow-hidden lg:col-span-2">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Anggota</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Anggota yang cocok</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach ($results['users'] as $member)
                            <div class="flex items-center justify-between gap-3 px-5 py-3">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ $member->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $member->email }} @if ($member->position) · {{ $member->position }} @endif</p>
                                </div>
                                <span class="ui-badge bg-slate-100 text-slate-600">{{ $member->role->label() }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        @if ($query !== '' && $total === 0)
            <div class="ui-card border-dashed p-10 text-center">
                <h2 class="text-sm font-bold text-slate-900">Tidak ada hasil</h2>
                <p class="mt-1 text-sm text-slate-500">Coba kata kunci lain atau periksa ejaan.</p>
            </div>
        @endif
    </div>
</x-app-layout>
