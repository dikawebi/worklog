<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Beranda / Dasbor</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900">Dasbor</h1>
                <p class="mt-1 text-sm text-slate-500">Ringkasan aktivitas dan progres tim IT.</p>
            </div>

            <a href="{{ route('worklogs.index') }}" class="ui-button-primary">
                {{ $hasTodayReport ? 'Lihat Worklog Hari Ini' : 'Isi Worklog Hari Ini' }}
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="brand-gradient overflow-hidden rounded-2xl px-6 py-7 text-white shadow-lg shadow-blue-900/10 sm:px-8">
            <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
                <div class="max-w-2xl">
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-200">
                        IT Worklog / Command Center
                    </p>
                    <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">
                        Selamat datang kembali, {{ auth()->user()->name }}.
                    </h2>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-blue-100">
                        Pantau pekerjaan harian, tugas, dan progres project tim dari satu ruang kerja.
                    </p>
                </div>

                @if ($hasTodayReport)
                    <span class="inline-flex w-fit items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3 py-1.5 text-xs font-bold text-white">
                        <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                        Worklog hari ini sudah diisi
                    </span>
                @else
                    <span class="inline-flex w-fit items-center gap-2 rounded-full border border-amber-300/40 bg-amber-400/15 px-3 py-1.5 text-xs font-bold text-amber-100">
                        <span class="h-2 w-2 rounded-full bg-amber-300"></span>
                        Worklog hari ini belum diisi
                    </span>
                @endif
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="ui-card border-l-4 border-l-brand p-5">
                <p class="text-sm font-medium text-slate-500">Aktivitas hari ini</p>
                <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $todayReports }}</p>
                <p class="mt-1 text-sm text-slate-500">Laporan yang sudah diisi</p>
            </div>

            <div class="ui-card border-l-4 border-l-amber-400 p-5">
                <p class="text-sm font-medium text-slate-500">Laporan tim</p>
                <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $todayReports }}/{{ $activeUsers }}</p>
                <p class="mt-1 text-sm text-slate-500">Anggota aktif hari ini</p>
            </div>

            <div class="ui-card border-l-4 border-l-rose-500 p-5">
                <p class="text-sm font-medium text-slate-500">Task overdue</p>
                <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $overdueTasks }}</p>
                <p class="mt-1 text-sm text-slate-500">Melewati deadline</p>
            </div>

            <div class="ui-card border-l-4 border-l-emerald-500 p-5">
                <p class="text-sm font-medium text-slate-500">Project aktif</p>
                <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $projectCount }}</p>
                <p class="mt-1 text-sm text-slate-500">Sedang berjalan</p>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="ui-card p-5">
                <p class="text-sm font-medium text-slate-500">Pekerjaan saya minggu ini</p>
                <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $myWeekEntries }}</p>
                <p class="mt-1 text-sm text-slate-500">Entri worklog pribadi</p>
            </div>
            <div class="ui-card p-5">
                <p class="text-sm font-medium text-slate-500">Hari terisi</p>
                <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $myWeekDays }}</p>
                <p class="mt-1 text-sm text-slate-500">Hari dengan worklog minggu ini</p>
            </div>
            <div class="ui-card p-5">
                <p class="text-sm font-medium text-slate-500">Task terbuka saya</p>
                <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $myOpenTasksCount }}</p>
                <p class="mt-1 text-sm text-slate-500">Belum selesai</p>
            </div>
        </section>

        @if ($canViewTeam)
            <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="ui-card p-5 sm:p-6">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Manager</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Belum isi hari ini</h2>
                        </div>
                        <span class="ui-badge {{ $pendingTodayUsers->isEmpty() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">{{ $pendingTodayUsers->count() }} anggota</span>
                    </div>

                    <div class="space-y-2">
                        @forelse ($pendingTodayUsers as $pendingUser)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-3 py-2">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ $pendingUser->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $pendingUser->position ?: 'Anggota tim' }}</p>
                                </div>
                                <span class="ui-badge bg-amber-100 text-amber-800">Belum</span>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-emerald-200 bg-emerald-50 px-4 py-6 text-center">
                                <p class="text-sm font-bold text-emerald-800">Semua anggota sudah mengisi worklog hari ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="ui-card p-5 sm:p-6">
                    <div class="mb-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Manager</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Aktivitas minggu ini</h2>
                    </div>

                    <div class="table-wrap overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                                <tr><th class="px-3 py-2">Anggota</th><th class="px-3 py-2">Pekerjaan</th><th class="px-3 py-2">Hari</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($weeklyActivity as $row)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <p class="font-semibold text-slate-900">{{ $row['name'] }}</p>
                                            <p class="text-xs text-slate-500">{{ $row['position'] ?: 'Anggota tim' }}</p>
                                        </td>
                                        <td class="px-3 py-2 font-bold text-slate-700">{{ $row['entries'] }}</td>
                                        <td class="px-3 py-2 text-slate-600">{{ $row['days'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-3 py-6 text-center text-sm text-slate-500">Belum ada aktivitas minggu ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="ui-card p-5 sm:p-6 xl:col-span-2">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Ruang kerja</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Aktivitas tim terbaru</h2>
                    </div>
                    <a href="{{ route('feed') }}" class="text-xs font-bold text-brand hover:text-brand-hover">Lihat semua</a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($recentReports as $report)
                        <div class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-soft text-sm font-extrabold text-brand">
                                {{ strtoupper(substr($report->user->name, 0, 1)) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-slate-900">
                                    {{ $report->user->name }}
                                    <span class="font-medium text-slate-400">· {{ $report->report_date->translatedFormat('d M Y') }}</span>
                                </p>
                                <p class="mt-0.5 text-xs font-semibold text-slate-500">
                                    {{ $report->entries_count }} pekerjaan dicatat
                                </p>
                                @if ($report->notes)
                                    <p class="mt-1 line-clamp-2 text-sm text-slate-600">{{ $report->notes }}</p>
                                @endif
                            </div>
                            <a href="{{ route('feed') }}" class="shrink-0 text-xs font-bold text-brand hover:text-brand-hover">Lihat</a>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-center">
                            <p class="text-sm font-bold text-slate-700">Belum ada aktivitas</p>
                            <p class="mt-1 text-xs text-slate-500">Worklog tim akan muncul di sini setelah diisi.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="ui-card p-5 sm:p-6">
                <div class="mb-5 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Prioritas</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Tugas saya</h2>
                    </div>
                    <a href="{{ route('tasks.index') }}" class="text-xs font-bold text-brand hover:text-brand-hover">Lihat semua</a>
                </div>

                <div class="space-y-3">
                    @forelse ($myOpenTasks as $task)
                        @php
                            $priorityTone = match ($task->priority->value) {
                                'urgent' => 'bg-rose-100 text-rose-700',
                                'high' => 'bg-amber-100 text-amber-800',
                                'low' => 'bg-slate-100 text-slate-600',
                                default => 'bg-sky-100 text-sky-800',
                            };
                        @endphp

                        <div class="rounded-xl border border-slate-200 p-3">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-bold text-slate-900">{{ $task->title }}</p>
                                <span class="ui-badge {{ $priorityTone }}">{{ $task->priority->label() }}</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $task->project?->code ?? 'Tanpa project' }}
                                @if ($task->due_date)
                                    · {{ $task->due_date->translatedFormat('d M') }}
                                @endif
                            </p>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-center">
                            <p class="text-sm font-bold text-slate-700">Tidak ada tugas terbuka</p>
                            <p class="mt-1 text-xs text-slate-500">Tugas yang diberikan kepadamu akan tampil di sini.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="ui-card p-5 sm:p-6">
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Monitoring</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Health project</h2>
                </div>
                <a href="{{ route('projects.index') }}" class="text-xs font-bold text-brand hover:text-brand-hover">Lihat project</a>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                @forelse ($activeProjects as $project)
                    @php
                        $progress = $project->tasks_count > 0
                            ? (int) round(($project->completed_tasks_count / $project->tasks_count) * 100)
                            : 0;
                    @endphp

                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-400">{{ $project->code }}</p>
                                <h3 class="mt-1 truncate text-sm font-bold text-slate-900">{{ $project->name }}</h3>
                            </div>
                            <span class="ui-badge bg-emerald-100 text-emerald-700">{{ $project->status->label() }}</span>
                        </div>

                        <div class="mt-4 flex items-center gap-3">
                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-brand" style="width: {{ $progress }}%"></div>
                            </div>
                            <span class="text-xs font-bold text-slate-600">{{ $progress }}%</span>
                        </div>

                        <p class="mt-2 text-xs text-slate-500">
                            {{ $project->completed_tasks_count }}/{{ $project->tasks_count }} task selesai
                            @if ($project->due_date)
                                · due {{ $project->due_date->translatedFormat('d M') }}
                            @endif
                        </p>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-center md:col-span-2">
                        <p class="text-sm font-bold text-slate-700">Belum ada project aktif</p>
                        <p class="mt-1 text-xs text-slate-500">Project yang berstatus aktif akan tampil di sini.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
