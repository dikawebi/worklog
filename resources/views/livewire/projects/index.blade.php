<div class="mx-auto max-w-6xl space-y-5" x-data="{ filtersOpen: false }" x-on:open-project-form.window="$wire.create()">
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

    <section class="ui-toolbar">
        <div class="flex gap-2">
            <div class="min-w-0 flex-1">
                <label for="project-search" class="sr-only">Cari project</label>
                <input id="project-search" type="search" wire:model.live.debounce.300ms="search" class="worklog-input" placeholder="Cari project..."></input>
            </div>
            <button type="button" @click="filtersOpen = ! filtersOpen" class="ui-button-secondary" :aria-expanded="filtersOpen">Filter</button>
        </div>
        <div x-show="filtersOpen" x-cloak class="ui-filter-drawer grid gap-3 sm:grid-cols-2">
            <div><label for="project-filter-status" class="worklog-label">Status</label><select id="project-filter-status" wire:model.live="statusFilter" class="worklog-input"><option value="">Semua status</option>@foreach ($statuses as $projectStatus)<option value="{{ $projectStatus->value }}">{{ $projectStatus->label() }}</option>@endforeach</select></div>
            <div><label for="project-filter-owner" class="worklog-label">Owner</label><select id="project-filter-owner" wire:model.live="ownerFilter" class="worklog-input"><option value="">Semua owner</option>@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div>
        </div>
    </section>

    <section class="ui-card overflow-hidden px-5">
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
            <article class="ui-list-row">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2"><p class="text-xs font-bold uppercase tracking-wide text-brand">{{ $project->code }}</p><span class="ui-badge {{ $statusTone }}">{{ $project->status->label() }}</span></div>
                        <h2 class="mt-1 truncate text-base font-bold text-slate-900">{{ $project->name }}</h2>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-slate-500">
                        <span>{{ $project->tasks_count }} task</span><span>{{ $project->members_count }} anggota</span><span>{{ $progress }}%</span>
                        <a href="{{ route('projects.show', $project) }}" class="font-bold text-brand hover:text-brand-hover">Buka →</a>
                    </div>
                </div>
                <div class="mt-3 h-1.5 rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand" style="width: {{ $progress }}%"></div></div>
                <p class="mt-2 truncate text-xs text-slate-500">{{ $project->description ?: 'Belum ada deskripsi project.' }} · Owner: {{ $project->owner->name }}</p>
            </article>
        @empty
            <div class="border-dashed p-10 text-center">
                <h2 class="text-sm font-bold text-slate-900">Belum ada project</h2>
                <p class="mt-1 text-sm text-slate-500">Project yang sesuai filter akan tampil di sini.</p>
            </div>
        @endforelse
    </section>

    @if ($projects->hasPages())
        <div>{{ $projects->links() }}</div>
    @endif
</div>
