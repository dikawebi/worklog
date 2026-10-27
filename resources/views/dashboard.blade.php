<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="app-eyebrow">Workspace</p>
                <h1 class="mt-1 text-xl font-bold tracking-tight text-slate-900">Selamat datang kembali, {{ auth()->user()->name }}.</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $canViewTeam ? 'Ringkasan singkat kondisi tim hari ini.' : 'Ini yang perlu kamu selesaikan hari ini.' }}</p>
            </div>
            <a href="{{ route('worklogs.index') }}" class="ui-button-primary">
                <span aria-hidden="true">+</span>
                {{ $hasTodayReport ? 'Buka worklog' : 'Catat pekerjaan' }}
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-5">
        <section class="ui-flat-panel grid grid-cols-2 divide-x divide-slate-100 overflow-hidden rounded-xl sm:grid-cols-4">
            @if ($canViewTeam)
                <div class="p-4 sm:p-5"><p class="text-xs font-semibold text-slate-500">Worklog tim</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ $todayReports }}/{{ $activeUsers }}</p><p class="mt-1 text-xs text-slate-500">terisi hari ini</p></div>
                <div class="p-4 sm:p-5"><p class="text-xs font-semibold text-slate-500">Perlu perhatian</p><p class="mt-1 text-2xl font-bold text-rose-600">{{ $overdueTasks }}</p><p class="mt-1 text-xs text-slate-500">task terlambat</p></div>
                <div class="p-4 sm:p-5"><p class="text-xs font-semibold text-slate-500">Project aktif</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ $projectCount }}</p><p class="mt-1 text-xs text-slate-500">sedang berjalan</p></div>
                <div class="p-4 sm:p-5"><p class="text-xs font-semibold text-slate-500">Aktivitas minggu ini</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ $weeklyActivity->sum('entries') }}</p><p class="mt-1 text-xs text-slate-500">pekerjaan tercatat</p></div>
            @else
                <div class="p-4 sm:p-5"><p class="text-xs font-semibold text-slate-500">Task terbuka</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ $myOpenTasksCount }}</p><p class="mt-1 text-xs text-slate-500">belum selesai</p></div>
                <div class="p-4 sm:p-5"><p class="text-xs font-semibold text-slate-500">Pekerjaan saya minggu ini</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ $myWeekEntries }}</p><p class="mt-1 text-xs text-slate-500">pekerjaan tercatat</p></div>
                <div class="p-4 sm:p-5"><p class="text-xs font-semibold text-slate-500">Hari terisi</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ $myWeekDays }}/6</p><p class="mt-1 text-xs text-slate-500">hari kerja minggu ini</p></div>
                <div class="p-4 sm:p-5"><p class="text-xs font-semibold text-slate-500">Project aktif</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ $projectCount }}</p><p class="mt-1 text-xs text-slate-500">yang sedang berjalan</p></div>
            @endif
        </section>

        @if ($canViewTeam)
            <section class="grid gap-5 lg:grid-cols-[1.1fr_.9fr]">
                <div class="ui-card p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div><p class="app-eyebrow">Action needed</p><h2 class="mt-1 text-base font-bold text-slate-900">Belum isi hari ini</h2></div>
                        <a href="{{ route('feed') }}" class="text-xs font-bold text-brand hover:text-brand-hover">Aktivitas tim →</a>
                    </div>
                    <div class="mt-4 divide-y divide-slate-100">
                        @forelse ($pendingTodayUsers as $pendingUser)
                            <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                                <span class="dashboard-avatar">{{ strtoupper(substr($pendingUser->name, 0, 1)) }}</span>
                                <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-slate-900">{{ $pendingUser->name }}</p><p class="truncate text-xs text-slate-500">{{ $pendingUser->position ?: 'Anggota tim' }}</p></div>
                                <span class="text-xs font-semibold text-amber-600">Belum</span>
                            </div>
                        @empty
                            <p class="py-6 text-sm text-emerald-700">Semua anggota sudah mengisi.</p>
                        @endforelse
                    </div>
                </div>

                <div class="ui-card p-5">
                    <div class="flex items-center justify-between gap-3"><div><p class="app-eyebrow">Team pulse</p><h2 class="mt-1 text-base font-bold text-slate-900">Aktivitas minggu ini</h2></div><a href="{{ route('reports.index') }}" class="text-xs font-bold text-brand">Laporan →</a></div>
                    <div class="mt-4 space-y-4">
                        @forelse ($weeklyActivity->take(5) as $row)
                            <div><div class="mb-1 flex justify-between gap-3 text-xs"><span class="truncate font-semibold text-slate-800">{{ $row['name'] }}</span><span class="shrink-0 text-slate-500">{{ $row['entries'] }} pekerjaan</span></div><div class="h-1.5 rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand" style="width: {{ min(100, $row['entries'] * 12) }}%"></div></div></div>
                        @empty
                            <p class="py-6 text-sm text-slate-500">Belum ada aktivitas minggu ini.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        @else
            <section class="grid gap-5 lg:grid-cols-[1.25fr_.75fr]">
                <div class="ui-card p-5">
                    <div class="flex items-center justify-between gap-3"><div><p class="app-eyebrow">Focus list</p><h2 class="mt-1 text-base font-bold text-slate-900">Task prioritas kamu</h2></div><a href="{{ route('tasks.index') }}" class="text-xs font-bold text-brand">Lihat semua →</a></div>
                    <div class="mt-3 divide-y divide-slate-100">
                        @forelse ($myOpenTasks as $task)
                            @php($isOverdue = $task->due_date && $task->due_date->isPast())
                            <a href="{{ route('tasks.show', $task) }}" class="ui-list-row flex items-center gap-3 first:pt-0 last:pb-0">
                                <span class="h-2 w-2 shrink-0 rounded-full {{ $task->priority->value === 'urgent' ? 'bg-rose-500' : ($task->priority->value === 'high' ? 'bg-amber-400' : 'bg-slate-300') }}"></span>
                                <span class="min-w-0 flex-1"><strong class="block truncate text-sm font-semibold text-slate-900">{{ $task->title }}</strong><small class="mt-1 block truncate text-xs text-slate-500">{{ $task->project?->code ?? 'Tanpa project' }}</small></span>
                                <span class="shrink-0 text-xs {{ $isOverdue ? 'font-bold text-rose-600' : 'text-slate-500' }}">{{ $task->due_date?->translatedFormat('d M') ?? '—' }}</span>
                            </a>
                        @empty
                            <p class="py-6 text-sm text-slate-500">Semua task selesai.</p>
                        @endforelse
                    </div>
                </div>
                <div class="ui-card p-5">
                    <div class="flex items-center justify-between gap-3"><div><p class="app-eyebrow">Your activity</p><h2 class="mt-1 text-base font-bold text-slate-900">Progress minggu ini</h2></div><span class="text-xs font-semibold text-brand">{{ $myWeekDays }}/6 hari</span></div>
                    <div class="dashboard-week-grid mt-5" aria-label="Progress worklog minggu ini">
                        @for ($day = 0; $day < 6; $day++)
                            @php($dayDate = now()->startOfWeek()->addDays($day))
                            @php($filled = in_array($dayDate->toDateString(), $myWeekDates, true))
                            <div class="dashboard-week-day {{ $filled && $dayDate->day <= today()->day ? 'is-filled' : '' }}"><span>{{ $dayDate->translatedFormat('D') }}</span><i></i><small>{{ $dayDate->format('d') }}</small></div>
                        @endfor
                    </div>
                    <a href="{{ route('worklogs.index') }}" class="ui-button-secondary mt-5 w-full">Buka worklog</a>
                </div>
            </section>
        @endif

        <section class="ui-card p-5">
            <div class="flex items-center justify-between gap-3"><div><p class="app-eyebrow">Projects</p><h2 class="mt-1 text-base font-bold text-slate-900">Project aktif</h2></div><a href="{{ route('projects.index') }}" class="text-xs font-bold text-brand">Lihat semua →</a></div>
            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($activeProjects as $project)
                    @php($progress = $project->tasks_count > 0 ? (int) round(($project->completed_tasks_count / $project->tasks_count) * 100) : 0)
                    <a href="{{ route('projects.show', $project) }}" class="ui-list-row block first:pt-0 last:pb-0">
                        <div class="flex items-center justify-between gap-3"><div class="min-w-0"><span class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ $project->code }}</span><h3 class="truncate text-sm font-semibold text-slate-900">{{ $project->name }}</h3></div><span class="text-xs font-bold text-slate-600">{{ $progress }}%</span></div>
                        <div class="mt-2 h-1.5 rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand" style="width: {{ $progress }}%"></div></div>
                        <p class="mt-1 text-xs text-slate-500">{{ $project->completed_tasks_count }}/{{ $project->tasks_count }} task selesai @if ($project->due_date) · deadline {{ $project->due_date->translatedFormat('d M') }} @endif</p>
                    </a>
                @empty
                    <p class="py-6 text-sm text-slate-500">Belum ada project aktif.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
