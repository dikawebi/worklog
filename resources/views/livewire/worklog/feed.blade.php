<div class="mx-auto max-w-5xl space-y-5" wire:loading.class="opacity-75">
    <section class="ui-card p-4 sm:p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Ringkasan aktivitas</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">{{ $rangeLabel }}</h2>
            </div>

            <div class="inline-flex w-full rounded-xl bg-slate-100 p-1 lg:w-auto" role="tablist" aria-label="Periode aktivitas">
                <button type="button" wire:click="setPeriod('today')" class="flex-1 rounded-lg px-4 py-2 text-xs font-bold transition lg:flex-none {{ $period === 'today' ? 'bg-white text-brand shadow-sm' : 'text-slate-500 hover:text-slate-900' }}" aria-selected="{{ $period === 'today' ? 'true' : 'false' }}">Hari ini</button>
                <button type="button" wire:click="setPeriod('week')" class="flex-1 rounded-lg px-4 py-2 text-xs font-bold transition lg:flex-none {{ $period === 'week' ? 'bg-white text-brand shadow-sm' : 'text-slate-500 hover:text-slate-900' }}" aria-selected="{{ $period === 'week' ? 'true' : 'false' }}">Minggu ini</button>
                <button type="button" wire:click="setPeriod('range')" class="flex-1 rounded-lg px-4 py-2 text-xs font-bold transition lg:flex-none {{ $period === 'range' ? 'bg-white text-brand shadow-sm' : 'text-slate-500 hover:text-slate-900' }}" aria-selected="{{ $period === 'range' ? 'true' : 'false' }}">Rentang</button>
            </div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="feed-from" class="worklog-label">Dari tanggal</label>
                <input id="feed-from" type="date" wire:model.live="from" max="{{ today()->format('Y-m-d') }}" class="worklog-input">
            </div>
            <div>
                <label for="feed-to" class="worklog-label">Sampai tanggal</label>
                <input id="feed-to" type="date" wire:model.live="to" max="{{ today()->format('Y-m-d') }}" class="worklog-input">
            </div>
            <div>
                <label for="feed-user" class="worklog-label">Anggota</label>
                <select id="feed-user" wire:model.live="userFilter" class="worklog-input">
                    <option value="">Semua anggota</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="feed-project" class="worklog-label">Project</label>
                <select id="feed-project" wire:model.live="projectFilter" class="worklog-input">
                    <option value="">Semua project</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->code }} — {{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Aktivitas terbaru</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $reports->total() }} laporan dengan pekerjaan aktif.</p>
        </div>
        <a href="{{ route('worklogs.index') }}" class="ui-button-primary">Isi Worklog</a>
    </div>

    <div class="space-y-4">
        @forelse ($reports as $report)
            <article class="ui-card p-4 transition hover:-translate-y-0.5 hover:shadow-md sm:p-5">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-soft text-sm font-extrabold text-brand">
                        {{ strtoupper(substr($report->user->name, 0, 1)) }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $report->user->name }}</p>
                                <p class="mt-0.5 text-xs font-semibold text-slate-500">{{ $report->report_date->translatedFormat('l, d F Y') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="ui-badge bg-brand-soft text-brand">{{ $report->entries_count }} pekerjaan</span>
                                @php($attachmentCount = $report->entries->sum('attachments_count'))
                                @if ($attachmentCount)
                                    <span class="ui-badge bg-slate-100 text-slate-600">📎 {{ $attachmentCount }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 space-y-3">
                            @foreach ($report->entries->take(3) as $entry)
                                <div class="border-l-2 border-brand/20 pl-3">
                                    <p class="text-sm leading-6 text-slate-700">{{ $entry->description }}</p>
                                    <div class="mt-1 flex flex-wrap gap-1.5">
                                        @if ($entry->project)
                                            <span class="ui-badge bg-blue-50 text-blue-700">{{ $entry->project->code }}</span>
                                        @endif
                                        @if ($entry->category)
                                            <span class="ui-badge bg-slate-100 text-slate-600">{{ $entry->category->name }}</span>
                                        @endif
                                        @if ($entry->attachments_count)
                                            <span class="ui-badge bg-amber-50 text-amber-700">📎 {{ $entry->attachments_count }} lampiran</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            @if ($report->entries_count > 3)
                                <p class="text-xs font-semibold text-slate-500">+ {{ $report->entries_count - 3 }} pekerjaan lainnya</p>
                            @endif
                        </div>

                        @if ($report->notes)
                            <div class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-xs leading-5 text-amber-800">
                                <span class="font-bold">Catatan:</span> {{ $report->notes }}
                            </div>
                        @endif

                        <div class="mt-4 flex justify-end">
                            <a href="{{ route('worklogs.show', $report) }}" class="text-xs font-bold text-brand hover:text-brand-hover">Lihat detail →</a>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="ui-card border-dashed p-10 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-soft text-2xl" aria-hidden="true">☷</span>
                <h3 class="mt-4 text-sm font-bold text-slate-900">Belum ada aktivitas</h3>
                <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">Belum ada worklog yang sesuai dengan filter ini.</p>
                <a href="{{ route('worklogs.index') }}" class="ui-button-primary mt-5">Catat Worklog</a>
            </div>
        @endforelse
    </div>

    @if ($reports->hasPages())
        <div>{{ $reports->links() }}</div>
    @endif
</div>
