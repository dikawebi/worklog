<div class="mx-auto max-w-6xl space-y-5" x-data x-on:open-project-form.window="$wire.create()">
    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">{{ $successMessage }}</div>
    @endif

    @if ($showForm)
        <section class="ui-card border-brand/30 p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">{{ $editingId ? 'Edit project' : 'Project baru' }}</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Informasi project</h2>
                </div>
                <button type="button" wire:click="cancelForm" class="text-sm font-bold text-slate-400 hover:text-slate-700" aria-label="Tutup form">✕</button>
            </div>

            <form wire:submit="save" class="mt-5 space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="project-code" class="worklog-label">Kode project <span class="text-rose-500">*</span></label>
                        <input id="project-code" type="text" wire:model="code" maxlength="32" class="worklog-input uppercase" placeholder="PRJ-001">
                        @error('code') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="project-name" class="worklog-label">Nama project <span class="text-rose-500">*</span></label>
                        <input id="project-name" type="text" wire:model="name" maxlength="255" class="worklog-input" placeholder="Migrasi Server">
                        @error('name') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="project-status" class="worklog-label">Status</label>
                        <select id="project-status" wire:model="status" class="worklog-input">
                            @foreach ($statuses as $projectStatus)
                                <option value="{{ $projectStatus->value }}">{{ $projectStatus->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="project-owner" class="worklog-label">Owner <span class="text-rose-500">*</span></label>
                        <select id="project-owner" wire:model="ownerId" class="worklog-input">
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('ownerId') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="project-start" class="worklog-label">Tanggal mulai</label>
                        <input id="project-start" type="date" wire:model="startDate" class="worklog-input">
                        @error('startDate') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="project-due" class="worklog-label">Deadline</label>
                        <input id="project-due" type="date" wire:model="dueDate" class="worklog-input">
                        @error('dueDate') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label for="project-description" class="worklog-label">Deskripsi</label>
                    <textarea id="project-description" wire:model="description" rows="3" maxlength="5000" class="worklog-input" placeholder="Tujuan dan ruang lingkup project."></textarea>
                    @error('description') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="cancelForm" class="ui-button-secondary">Batal</button>
                    <button type="submit" class="ui-button-primary" wire:loading.attr="disabled">Simpan Project</button>
                </div>
            </form>
        </section>
    @endif

    <section class="ui-card p-4 sm:p-5">
        <div class="grid gap-3 md:grid-cols-3">
            <div class="md:col-span-1">
                <label for="project-search" class="sr-only">Cari project</label>
                <input id="project-search" type="search" wire:model.live.debounce.300ms="search" class="worklog-input" placeholder="Cari kode atau nama project..."></input>
            </div>
            <div>
                <label for="project-filter-status" class="sr-only">Filter status</label>
                <select id="project-filter-status" wire:model.live="statusFilter" class="worklog-input">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $projectStatus)
                        <option value="{{ $projectStatus->value }}">{{ $projectStatus->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="project-filter-owner" class="sr-only">Filter owner</label>
                <select id="project-filter-owner" wire:model.live="ownerFilter" class="worklog-input">
                    <option value="">Semua owner</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($projects as $project)
            @php
                $progress = $project->tasks_count > 0 ? (int) round(($project->completed_tasks_count ?? 0) / $project->tasks_count * 100) : 0;
                $statusTone = match ($project->status->value) {
                    'active' => 'bg-emerald-100 text-emerald-700',
                    'on_hold' => 'bg-amber-100 text-amber-800',
                    'completed' => 'bg-blue-100 text-blue-700',
                    'cancelled' => 'bg-rose-100 text-rose-700',
                    default => 'bg-slate-100 text-slate-600',
                };
            @endphp
            <article class="ui-card flex flex-col p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-brand">{{ $project->code }}</p>
                        <h2 class="mt-1 truncate text-base font-bold text-slate-900">{{ $project->name }}</h2>
                    </div>
                    <span class="ui-badge shrink-0 {{ $statusTone }}">{{ $project->status->label() }}</span>
                </div>
                <p class="mt-3 line-clamp-2 min-h-10 text-sm leading-5 text-slate-500">{{ $project->description ?: 'Belum ada deskripsi project.' }}</p>
                <div class="mt-4 flex items-center gap-3">
                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand" style="width: {{ $progress }}%"></div></div>
                    <span class="text-xs font-bold text-slate-600">{{ $progress }}%</span>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-2 border-y border-slate-100 py-3 text-center text-xs">
                    <div><p class="font-bold text-slate-900">{{ $project->members_count }}</p><p class="text-slate-500">Anggota</p></div>
                    <div><p class="font-bold text-slate-900">{{ $project->tasks_count }}</p><p class="text-slate-500">Task</p></div>
                    <div><p class="font-bold text-slate-900">{{ $project->milestones_count }}</p><p class="text-slate-500">Milestone</p></div>
                </div>
                <div class="mt-auto flex items-center justify-between gap-3 pt-4">
                    <p class="truncate text-xs text-slate-500">Owner: {{ $project->owner->name }}</p>
                    <a href="{{ route('projects.show', $project) }}" class="shrink-0 text-xs font-bold text-brand hover:text-brand-hover">Detail →</a>
                </div>
            </article>
        @empty
            <div class="ui-card border-dashed p-10 text-center md:col-span-2 xl:col-span-3">
                <h2 class="text-sm font-bold text-slate-900">Belum ada project</h2>
                <p class="mt-1 text-sm text-slate-500">Project yang sesuai filter akan tampil di sini.</p>
            </div>
        @endforelse
    </div>

    @if ($projects->hasPages())
        <div>{{ $projects->links() }}</div>
    @endif
</div>
