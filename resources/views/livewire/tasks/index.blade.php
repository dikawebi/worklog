<div class="mx-auto max-w-7xl space-y-5">
    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">{{ $successMessage }}</div>
    @endif

    @error('status')
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800" role="alert">{{ $message }}</div>
    @enderror

    @if ($showForm)
        <section class="ui-card border-brand/30 p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">{{ $editingId ? 'Edit task' : 'Task baru' }}</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Informasi task</h2>
                </div>
                <button type="button" wire:click="cancelForm" class="text-sm font-bold text-slate-400 hover:text-slate-700">Tutup</button>
            </div>

            <form wire:submit="save" class="mt-5 space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="task-project" class="worklog-label">Project <span class="text-rose-500">*</span></label>
                        <select id="task-project" wire:model.live="projectId" class="worklog-input">
                            <option value="">Pilih project</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->code }} — {{ $project->name }}</option>
                            @endforeach
                        </select>
                        @error('projectId') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="task-milestone" class="worklog-label">Milestone</label>
                        <select id="task-milestone" wire:model="milestoneId" class="worklog-input" @disabled(! $projectId)>
                            <option value="">Tanpa milestone</option>
                            @foreach ($milestones as $milestone)
                                <option value="{{ $milestone->id }}">{{ $milestone->title }}</option>
                            @endforeach
                        </select>
                        @error('milestoneId') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="task-title" class="worklog-label">Judul task <span class="text-rose-500">*</span></label>
                        <input id="task-title" type="text" wire:model="title" maxlength="255" class="worklog-input" placeholder="Menyiapkan server staging">
                        @error('title') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="task-assignee" class="worklog-label">Assignee</label>
                        <select id="task-assignee" wire:model="assigneeId" class="worklog-input">
                            <option value="">Belum ditugaskan</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('assigneeId') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="task-priority" class="worklog-label">Prioritas</label>
                        <select id="task-priority" wire:model="priority" class="worklog-input">
                            @foreach ($priorities as $priorityOption)
                                <option value="{{ $priorityOption->value }}">{{ $priorityOption->label() }}</option>
                            @endforeach
                        </select>
                        @error('priority') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="task-due" class="worklog-label">Deadline</label>
                        <input id="task-due" type="date" wire:model="dueDate" class="worklog-input">
                        @error('dueDate') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="task-description" class="worklog-label">Deskripsi</label>
                        <textarea id="task-description" wire:model="description" rows="3" maxlength="5000" class="worklog-input" placeholder="Konteks dan hasil yang diharapkan."></textarea>
                        @error('description') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if (! $editingId)
                    <p class="text-xs text-slate-500">Status awal task adalah <strong>Belum dikerjakan</strong>. Status diubah melalui tombol pada daftar task.</p>
                @endif

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="cancelForm" class="ui-button-secondary">Batal</button>
                    <button type="submit" class="ui-button-primary" wire:loading.attr="disabled">
                        {{ $editingId ? 'Simpan Perubahan' : 'Buat Task' }}
                    </button>
                </div>
            </form>
        </section>
    @endif

    <section class="ui-card p-4 sm:p-5">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
            <div class="xl:col-span-2">
                <label for="task-search" class="sr-only">Cari task</label>
                <input id="task-search" type="search" wire:model.live.debounce.300ms="search" class="worklog-input" placeholder="Cari judul atau deskripsi...">
            </div>
            <div>
                <label for="task-status-filter" class="sr-only">Status</label>
                <select id="task-status-filter" wire:model.live="statusFilter" class="worklog-input">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="task-priority-filter" class="sr-only">Prioritas</label>
                <select id="task-priority-filter" wire:model.live="priorityFilter" class="worklog-input">
                    <option value="">Semua prioritas</option>
                    @foreach ($priorities as $priorityOption)
                        <option value="{{ $priorityOption->value }}">{{ $priorityOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="task-project-filter" class="sr-only">Project</label>
                <select id="task-project-filter" wire:model.live="projectFilter" class="worklog-input">
                    <option value="">Semua project</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->code }}</option>
                    @endforeach
                </select>
            </div>
            @if (auth()->user()->isManager() || auth()->user()->isAdmin())
                <div>
                    <label for="task-assignee-filter" class="sr-only">Assignee</label>
                    <select id="task-assignee-filter" wire:model.live="assigneeFilter" class="worklog-input">
                        <option value="">Semua assignee</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="flex items-center">
                    <p class="text-xs font-semibold text-slate-500">Menampilkan task yang ditugaskan kepada Anda.</p>
                </div>
            @endif
        </div>

        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                <input type="checkbox" wire:model.live="overdueOnly" class="rounded border-slate-300 text-brand focus:ring-brand">
                Hanya yang terlambat
            </label>

            <div class="flex items-center gap-2">
                <div class="inline-flex rounded-lg border border-slate-200 bg-white p-1">
                    <button type="button" wire:click="$set('viewMode', 'list')" class="rounded-md px-3 py-1.5 text-xs font-bold {{ $viewMode === 'list' ? 'bg-brand text-white' : 'text-slate-600 hover:bg-slate-50' }}">List</button>
                    <button type="button" wire:click="$set('viewMode', 'board')" class="rounded-md px-3 py-1.5 text-xs font-bold {{ $viewMode === 'board' ? 'bg-brand text-white' : 'text-slate-600 hover:bg-slate-50' }}">Board</button>
                </div>
                @if ($canCreate)
                    <button type="button" wire:click="create" class="ui-button-primary">+ Task</button>
                @endif
            </div>
        </div>
    </section>

    @if ($viewMode === 'board')
        <div class="task-board -mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-2 xl:mx-0 xl:grid xl:grid-cols-4 xl:overflow-visible xl:px-0" x-data="{ dragging: null }">
            @foreach ($statuses as $statusOption)
                @php($columnTasks = $boardTasks[$statusOption->value] ?? collect())
                <section
                    class="min-w-[280px] snap-start rounded-2xl border border-slate-200 bg-slate-50/80 p-3 transition xl:min-w-0"
                    @dragover.prevent="$el.classList.add('border-brand/50', 'bg-brand-soft/30')"
                    @dragleave="$el.classList.remove('border-brand/50', 'bg-brand-soft/30')"
                    @drop.prevent="$el.classList.remove('border-brand/50', 'bg-brand-soft/30'); if (dragging) { $wire.changeStatus(dragging, '{{ $statusOption->value }}'); dragging = null; }"
                >
                    <div class="flex items-center justify-between gap-2 px-1 py-1">
                        <h2 class="text-sm font-bold text-slate-800">{{ $statusOption->label() }}</h2>
                        <span class="ui-badge bg-white text-slate-600 ring-1 ring-slate-200">{{ $columnTasks->count() }}</span>
                    </div>

                    <div class="mt-3 space-y-3">
                        @forelse ($columnTasks as $task)
                            <article
                                class="ui-card cursor-grab p-4 active:cursor-grabbing"
                                wire:key="board-task-{{ $task->id }}"
                                draggable="true"
                                @dragstart="dragging = {{ $task->id }}"
                                @dragend="dragging = null"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-xs font-bold uppercase tracking-wide text-brand">{{ $task->project?->code ?? 'Tanpa project' }}</p>
                                    <span class="ui-badge {{ $task->priority->value === 'urgent' ? 'bg-rose-100 text-rose-700' : ($task->priority->value === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">{{ $task->priority->label() }}</span>
                                </div>
                                <h3 class="mt-2 text-sm font-bold leading-5 text-slate-900">
                                    <a href="{{ route('tasks.show', $task) }}" class="hover:text-brand">{{ $task->title }}</a>
                                </h3>
                                @if ($task->description)
                                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ $task->description }}</p>
                                @endif
                                <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                    <span>{{ $task->assignee?->name ?? 'Belum ditugaskan' }}</span>
                                    @if ($task->due_date)
                                        <span>·</span>
                                        <span class="{{ $task->due_date->isPast() && $task->status->value !== 'done' ? 'font-bold text-rose-600' : '' }}">{{ $task->due_date->translatedFormat('d M Y') }}</span>
                                    @endif
                                </div>
                                <div class="mt-3 flex flex-wrap gap-1.5 border-t border-slate-100 pt-3">
                                    @can('changeStatus', $task)
                                        @php($nextStatuses = match ($task->status->value) {
                                            'todo' => ['in_progress' => 'Mulai'],
                                            'in_progress' => ['blocked' => 'Terhambat', 'done' => 'Selesai'],
                                            'blocked' => ['in_progress' => 'Lanjutkan'],
                                            default => [],
                                        })
                                        @foreach ($nextStatuses as $nextStatus => $label)
                                            <button type="button" wire:click="changeStatus({{ $task->id }}, '{{ $nextStatus }}')" class="ui-button-secondary !px-2.5 !py-1 text-[11px]">{{ $label }}</button>
                                        @endforeach
                                    @endcan
                                    @can('update', $task)
                                        <button type="button" wire:click="edit({{ $task->id }})" class="ui-button-secondary !px-2.5 !py-1 text-[11px]">Edit</button>
                                    @endcan
                                    @can('delete', $task)
                                        <button type="button" wire:click="delete({{ $task->id }})" wire:confirm="Hapus task ini?" class="ui-button-secondary !px-2.5 !py-1 text-[11px] text-rose-600">Hapus</button>
                                    @endcan
                                </div>
                            </article>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 bg-white px-3 py-6 text-center text-xs text-slate-500">Belum ada task.</div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <section class="ui-card overflow-hidden">
            <div class="table-wrap overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Task</th>
                            <th class="px-4 py-3">Project</th>
                            <th class="px-4 py-3">Assignee</th>
                            <th class="px-4 py-3">Prioritas</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Deadline</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tasks as $task)
                            <tr wire:key="task-{{ $task->id }}">
                                <td class="px-4 py-3">
                                    <a href="{{ route('tasks.show', $task) }}" class="font-bold text-slate-900 hover:text-brand">{{ $task->title }}</a>
                                    @if ($task->milestone)
                                        <p class="text-xs text-slate-500">{{ $task->milestone->title }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs font-semibold text-slate-600">
                                    {{ $task->project?->code ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600">{{ $task->assignee?->name ?? 'Belum ditugaskan' }}</td>
                                <td class="px-4 py-3">
                                    <span class="ui-badge {{ $task->priority->value === 'urgent' ? 'bg-rose-100 text-rose-700' : ($task->priority->value === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">{{ $task->priority->label() }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="ui-badge {{ $task->status->value === 'done' ? 'bg-emerald-100 text-emerald-700' : ($task->status->value === 'blocked' ? 'bg-rose-100 text-rose-700' : ($task->status->value === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600')) }}">{{ $task->status->label() }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs {{ $task->due_date && $task->due_date->isPast() && $task->status->value !== 'done' ? 'font-bold text-rose-600' : 'text-slate-500' }}">
                                    {{ $task->due_date?->translatedFormat('d M Y') ?? '—' }}
                                    @if ($task->due_date && $task->due_date->isPast() && $task->status->value !== 'done')
                                        <span class="ml-1">Terlambat</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap justify-end gap-1.5">
                                        @can('changeStatus', $task)
                                            @if ($task->status->value === 'todo')
                                                <button type="button" wire:click="changeStatus({{ $task->id }}, 'in_progress')" class="ui-button-secondary !px-2.5 !py-1 text-[11px]">Mulai</button>
                                            @elseif ($task->status->value === 'in_progress')
                                                <button type="button" wire:click="changeStatus({{ $task->id }}, 'done')" class="ui-button-secondary !px-2.5 !py-1 text-[11px]">Selesai</button>
                                                <button type="button" wire:click="changeStatus({{ $task->id }}, 'blocked')" class="ui-button-secondary !px-2.5 !py-1 text-[11px]">Terhambat</button>
                                            @elseif ($task->status->value === 'blocked')
                                                <button type="button" wire:click="changeStatus({{ $task->id }}, 'in_progress')" class="ui-button-secondary !px-2.5 !py-1 text-[11px]">Lanjutkan</button>
                                            @endif
                                        @endcan
                                        @can('update', $task)
                                            <button type="button" wire:click="edit({{ $task->id }})" class="ui-button-secondary !px-2.5 !py-1 text-[11px]">Edit</button>
                                        @endcan
                                        @can('delete', $task)
                                            <button type="button" wire:click="delete({{ $task->id }})" wire:confirm="Hapus task ini?" class="ui-button-secondary !px-2.5 !py-1 text-[11px] text-rose-600">Hapus</button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">Tidak ada task yang sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($tasks->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">{{ $tasks->links() }}</div>
            @endif
        </section>
    @endif
</div>
