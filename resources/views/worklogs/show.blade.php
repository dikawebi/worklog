<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Aktivitas tim / Detail</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900">Worklog {{ $report->user->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $report->report_date->translatedFormat('l, d F Y') }} · {{ $report->entries->count() }} pekerjaan</p>
            </div>
            <a href="{{ route('feed') }}" class="ui-button-secondary">Kembali ke Aktivitas Tim</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-5">
        <section class="ui-card flex items-center gap-3 p-5">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-soft text-lg font-extrabold text-brand">{{ strtoupper(substr($report->user->name, 0, 1)) }}</span>
            <div>
                <h2 class="font-bold text-slate-900">{{ $report->user->name }}</h2>
                <p class="text-sm text-slate-500">{{ $report->user->position ?: 'Anggota tim IT' }}</p>
            </div>
        </section>

        <section class="space-y-3">
            @foreach ($report->entries as $index => $entry)
                <article class="ui-card p-5">
                    <div class="flex items-start gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-soft text-sm font-extrabold text-brand">{{ $index + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap gap-1.5">
                                @if ($entry->project)
                                    <span class="ui-badge bg-blue-50 text-blue-700">{{ $entry->project->code }} — {{ $entry->project->name }}</span>
                                @endif
                                @if ($entry->task)
                                    <span class="ui-badge bg-violet-50 text-violet-700">{{ $entry->task->title }}</span>
                                @endif
                                @if ($entry->category)
                                    <span class="ui-badge bg-slate-100 text-slate-600">{{ $entry->category->name }}</span>
                                @endif
                            </div>
                            <p class="mt-3 text-sm leading-7 text-slate-700">{{ $entry->description }}</p>

                            @if ($entry->attachments->isNotEmpty())
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($entry->attachments as $attachment)
                                        <a href="{{ route('attachments.download', $attachment) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 hover:border-brand/40 hover:text-brand">
                                            <span aria-hidden="true">📎</span>{{ $attachment->original_name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        @if ($report->notes)
            <section class="ui-card border-l-4 border-l-amber-400 p-5">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-amber-700">Catatan / blocker</p>
                <p class="mt-2 text-sm leading-6 text-slate-700">{{ $report->notes }}</p>
            </section>
        @endif

        <livewire:comments :commentable-type="\App\Models\DailyReport::class" :commentable-id="$report->id" />
    </div>
</x-app-layout>
