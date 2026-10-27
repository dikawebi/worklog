<div class="mx-auto max-w-7xl space-y-5" x-data="{ filtersOpen: false }">
    <section class="ui-toolbar">
        <div class="flex flex-wrap items-center gap-2">
            <span class="me-1 text-xs font-semibold text-slate-500">Periode</span>
            @foreach (['today' => 'Hari ini', 'week' => 'Minggu ini', 'month' => 'Bulan ini'] as $periodKey => $periodLabel)
                <button type="button" wire:click="setPeriod('{{ $periodKey }}')" class="rounded-lg px-3 py-2 text-xs font-bold transition {{ $period === $periodKey ? 'bg-brand text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:border-brand/40 hover:text-brand' }}">{{ $periodLabel }}</button>
            @endforeach
            <button type="button" wire:click="$set('period', 'custom')" class="rounded-lg px-3 py-2 text-xs font-bold transition {{ $period === 'custom' ? 'bg-slate-800 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:border-brand/40 hover:text-brand' }}">Custom</button>
            <button type="button" @click="filtersOpen = ! filtersOpen" class="ui-button-secondary ms-auto" :aria-expanded="filtersOpen">Filter</button>
        </div>
        <div x-show="filtersOpen" x-cloak class="ui-filter-drawer grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @if ($period === 'custom')
                <div><label for="report-from" class="worklog-label">Dari tanggal</label><input id="report-from" type="date" wire:model.live="from" class="worklog-input"></div>
                <div><label for="report-to" class="worklog-label">Sampai tanggal</label><input id="report-to" type="date" wire:model.live="to" class="worklog-input"></div>
            @endif
            @if ($canViewAll)
                <div><label for="report-user" class="worklog-label">Anggota</label><select id="report-user" wire:model.live="userFilter" class="worklog-input"><option value="">Semua anggota</option>@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div>
            @endif
            <div><label for="report-project" class="worklog-label">Project</label><select id="report-project" wire:model.live="projectFilter" class="worklog-input"><option value="">Semua project</option>@foreach ($projects as $project)<option value="{{ $project->id }}">{{ $project->code }} — {{ $project->name }}</option>@endforeach</select></div>
            <div><label for="report-category" class="worklog-label">Kategori</label><select id="report-category" wire:model.live="categoryFilter" class="worklog-input"><option value="">Semua kategori</option>@foreach ($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
            <button type="button" wire:click="resetFilters" class="text-left text-xs font-bold text-brand">Reset semua filter</button>
        </div>
        <p class="mt-3 text-xs text-slate-500">{{ \Carbon\Carbon::parse($filters['from'])->translatedFormat('d M Y') }} — {{ \Carbon\Carbon::parse($filters['to'])->translatedFormat('d M Y') }} · {{ $summary['entries'] }} pekerjaan.</p>
    </section>

    <section class="ui-flat-panel grid grid-cols-2 divide-x divide-y divide-slate-100 overflow-hidden rounded-xl sm:grid-cols-4 sm:divide-y-0">
        <div class="p-4 sm:p-5">
            <p class="text-sm font-medium text-slate-500">Jumlah pekerjaan</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary['entries']) }}</p>
            <p class="mt-1 text-sm text-slate-500">Entri pekerjaan tercatat</p>
        </div>
        <div class="p-4 sm:p-5">
            <p class="text-sm font-medium text-slate-500">Hari terisi</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary['days']) }}</p>
            <p class="mt-1 text-sm text-slate-500">Tanggal dengan worklog</p>
        </div>
        <div class="p-4 sm:p-5">
            <p class="text-sm font-medium text-slate-500">Anggota terlibat</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary['members']) }}</p>
            <p class="mt-1 text-sm text-slate-500">Anggota yang mencatat</p>
        </div>
        <div class="p-4 sm:p-5">
            <p class="text-sm font-medium text-slate-500">Rata-rata per hari</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $summary['days'] > 0 ? number_format($summary['entries'] / $summary['days'], 1) : '0' }}</p>
            <p class="mt-1 text-sm text-slate-500">Pekerjaan per hari terisi</p>
        </div>
    </section>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">Menampilkan {{ $entries->firstItem() ?? 0 }}–{{ $entries->lastItem() ?? 0 }} dari {{ $entries->total() }} pekerjaan.</p>
        <div class="flex gap-2">
            <a href="{{ $exportUrl }}" class="ui-button-secondary">Export CSV</a>
            <a href="{{ $printUrl }}" target="_blank" class="ui-button-secondary">Print / PDF</a>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        <section class="ui-card overflow-hidden">
            <div class="border-b border-slate-100 p-5">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Per anggota</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">Aktivitas anggota</h2>
            </div>
            <div class="table-wrap overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Anggota</th>
                            <th class="px-4 py-3">Pekerjaan</th>
                            <th class="px-4 py-3">Hari terisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($perUser as $row)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-bold text-slate-900">{{ $row->name }}</p>
                                    @if ($row->position)
                                        <p class="text-xs text-slate-500">{{ $row->position }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-700">{{ $row->entries }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $row->days }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ui-card overflow-hidden">
            <div class="border-b border-slate-100 p-5">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Per project</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">Distribusi pekerjaan</h2>
            </div>
            <div class="table-wrap overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Project</th>
                            <th class="px-4 py-3">Pekerjaan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($perProject as $row)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-bold text-slate-900">{{ $row->code ?? 'Tanpa project' }}</p>
                                    @if ($row->name)
                                        <p class="text-xs text-slate-500">{{ $row->name }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-700">{{ $row->entries }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="ui-card overflow-hidden">
        <div class="border-b border-slate-100 p-5">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Detail</p>
            <h2 class="mt-1 text-lg font-bold text-slate-900">Pekerjaan tercatat</h2>
        </div>
        <div class="table-wrap overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Anggota</th>
                        <th class="px-4 py-3">Project</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Pekerjaan</th>
                        <th class="px-4 py-3">Lampiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($entries as $entry)
                        <tr wire:key="report-entry-{{ $entry->id }}">
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500">{{ $entry->dailyReport?->report_date?->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 text-xs font-semibold text-slate-700">{{ $entry->dailyReport?->user?->name }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600">
                                {{ $entry->project?->code ?? '—' }}
                                @if ($entry->project?->name)
                                    <p class="text-slate-400">{{ $entry->project->name }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($entry->category)
                                    <span class="ui-badge bg-slate-100 text-slate-600">{{ $entry->category->name }}</span>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $entry->description }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $entry->attachments_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">Tidak ada pekerjaan pada filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($entries->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">{{ $entries->links() }}</div>
        @endif
    </section>
</div>
