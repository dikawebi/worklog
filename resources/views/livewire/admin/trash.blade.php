<div class="mx-auto max-w-6xl space-y-5">
    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">{{ $successMessage }}</div>
    @endif

    <section class="ui-card p-4 sm:p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="inline-flex flex-wrap gap-1 rounded-xl border border-slate-200 bg-white p-1">
                @foreach ([
                    'reports' => ['label' => 'Laporan', 'count' => $counts['reports']],
                    'entries' => ['label' => 'Pekerjaan', 'count' => $counts['entries']],
                    'attachments' => ['label' => 'Lampiran', 'count' => $counts['attachments']],
                ] as $key => $item)
                    <button type="button" wire:click="setTab('{{ $key }}')" class="rounded-lg px-3 py-2 text-xs font-bold {{ $tab === $key ? 'bg-brand text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                        {{ $item['label'] }} <span class="ml-1 opacity-75">{{ $item['count'] }}</span>
                    </button>
                @endforeach
            </div>

            <div class="w-full lg:max-w-sm">
                <label for="trash-search" class="sr-only">Cari data terhapus</label>
                <input id="trash-search" type="search" wire:model.live.debounce.300ms="search" class="worklog-input" placeholder="Cari data terhapus...">
            </div>
        </div>
        <p class="mt-3 text-xs text-slate-500">Data yang dipulihkan akan kembali tampil di aplikasi. Tindakan pemulihan tercatat di audit log.</p>
    </section>

    <section class="ui-card overflow-hidden">
        <div class="table-wrap overflow-x-auto">
            @if ($tab === 'reports')
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr><th class="px-4 py-3">Anggota</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Pekerjaan</th><th class="px-4 py-3">Dihapus</th><th class="px-4 py-3 text-right">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($reports as $report)
                            <tr wire:key="trash-report-{{ $report->id }}">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $report->user?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $report->report_date?->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $report->entries_count }}</td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ $report->deleted_at?->translatedFormat('d M Y H:i') }}</td>
                                <td class="px-4 py-3 text-right"><button type="button" wire:click="restoreReport({{ $report->id }})" class="ui-button-secondary !px-3 !py-1.5 text-xs">Pulihkan</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">Tidak ada laporan terhapus.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif ($tab === 'entries')
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Anggota</th><th class="px-4 py-3">Pekerjaan</th><th class="px-4 py-3">Dihapus</th><th class="px-4 py-3 text-right">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($entries as $entry)
                            <tr wire:key="trash-entry-{{ $entry->id }}">
                                <td class="px-4 py-3 text-slate-600">{{ $entry->dailyReport?->report_date?->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $entry->dailyReport?->user?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $entry->description }}</td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ $entry->deleted_at?->translatedFormat('d M Y H:i') }}</td>
                                <td class="px-4 py-3 text-right"><button type="button" wire:click="restoreEntry({{ $entry->id }})" class="ui-button-secondary !px-3 !py-1.5 text-xs">Pulihkan</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">Tidak ada pekerjaan terhapus.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr><th class="px-4 py-3">Nama file</th><th class="px-4 py-3">Diunggah oleh</th><th class="px-4 py-3">Ukuran</th><th class="px-4 py-3">Dihapus</th><th class="px-4 py-3 text-right">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($attachments as $attachment)
                            <tr wire:key="trash-attachment-{{ $attachment->id }}">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $attachment->original_name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $attachment->user?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ number_format($attachment->size / 1024, 1) }} KB</td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ $attachment->deleted_at?->translatedFormat('d M Y H:i') }}</td>
                                <td class="px-4 py-3 text-right"><button type="button" wire:click="restoreAttachment({{ $attachment->id }})" class="ui-button-secondary !px-3 !py-1.5 text-xs">Pulihkan</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">Tidak ada lampiran terhapus.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </section>
</div>
