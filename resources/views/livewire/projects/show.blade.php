<div class="mx-auto max-w-6xl space-y-5">
    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">{{ $successMessage }}</div>
    @endif

    <section class="brand-gradient rounded-2xl p-6 text-white shadow-lg shadow-blue-900/10 sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full border border-white/25 bg-white/10 px-3 py-1 text-xs font-bold">{{ $project->code }}</span>
                    <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold">{{ $project->status->label() }}</span>
                </div>
                <h2 class="mt-4 text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $project->name }}</h2>
                <p class="mt-2 text-sm leading-6 text-blue-100">{{ $project->description ?: 'Belum ada deskripsi project.' }}</p>
                <p class="mt-4 text-xs font-semibold text-blue-200">Owner: {{ $project->owner->name }} · {{ $project->members_count }} anggota</p>
            </div>
            @can('update', $project)
                <button type="button" wire:click="startEditing" class="rounded-lg border border-white/25 bg-white/10 px-4 py-2 text-xs font-bold text-white transition hover:bg-white/20">Edit Project</button>
            @endcan
        </div>
        <div class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([['label' => 'Task', 'value' => $project->tasks_count], ['label' => 'Selesai', 'value' => $project->completed_tasks_count], ['label' => 'Milestone', 'value' => $project->milestones_count], ['label' => 'Aktivitas', 'value' => $project->work_log_entries_count]] as $metric)
                <div class="rounded-xl border border-white/15 bg-white/10 p-3"><p class="text-2xl font-extrabold">{{ $metric['value'] }}</p><p class="mt-1 text-xs font-semibold text-blue-100">{{ $metric['label'] }}</p></div>
            @endforeach
        </div>
    </section>

    @if ($editing)
        <section class="ui-card p-5 sm:p-6">
            <div class="flex items-center justify-between"><h2 class="text-lg font-bold text-slate-900">Edit informasi project</h2><button type="button" wire:click="cancelEditing" class="text-sm font-bold text-slate-400">✕</button></div>
            <form wire:submit="saveProject" class="mt-5 space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div><label for="show-name" class="worklog-label">Nama project</label><input id="show-name" type="text" wire:model="name" class="worklog-input">@error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                    <div><label for="show-status" class="worklog-label">Status</label><select id="show-status" wire:model="status" class="worklog-input">@foreach ($statuses as $projectStatus)<option value="{{ $projectStatus->value }}">{{ $projectStatus->label() }}</option>@endforeach</select></div>
                    <div><label for="show-owner" class="worklog-label">Owner</label><select id="show-owner" wire:model="ownerId" class="worklog-input">@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div>
                    <div><label for="show-due" class="worklog-label">Deadline</label><input id="show-due" type="date" wire:model="dueDate" class="worklog-input">@error('dueDate')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                </div>
                <div><label for="show-description" class="worklog-label">Deskripsi</label><textarea id="show-description" wire:model="description" rows="3" class="worklog-input"></textarea></div>
                <div class="flex justify-end gap-2"><button type="button" wire:click="cancelEditing" class="ui-button-secondary">Batal</button><button type="submit" class="ui-button-primary">Simpan Perubahan</button></div>
            </form>
        </section>
    @endif

    <div class="grid gap-5 xl:grid-cols-3">
        <section class="ui-card p-5 xl:col-span-2">
            <div class="flex items-end justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Timeline</p><h2 class="mt-1 text-lg font-bold text-slate-900">Milestone</h2></div>@can('update', $project)<button type="button" wire:click="startMilestoneForm" class="ui-button-primary">+ Milestone</button>@endcan</div>

            @if ($showMilestoneForm)
                <form wire:submit="saveMilestone" class="mt-5 rounded-xl border border-brand/20 bg-brand-soft/30 p-4 space-y-4">
                    <div><label for="milestone-title" class="worklog-label">Nama milestone</label><input id="milestone-title" type="text" wire:model="milestoneTitle" class="worklog-input" placeholder="UAT dan serah terima"><p class="mt-1 text-xs text-rose-600">@error('milestoneTitle'){{ $message }}@enderror</p></div>
                    <div class="grid gap-4 sm:grid-cols-2"><div><label for="milestone-status" class="worklog-label">Status</label><select id="milestone-status" wire:model="milestoneStatus" class="worklog-input">@foreach ($milestoneStatuses as $statusOption)<option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>@endforeach</select></div><div><label for="milestone-due" class="worklog-label">Deadline</label><input id="milestone-due" type="date" wire:model="milestoneDueDate" class="worklog-input"></div></div>
                    <div><label for="milestone-description" class="worklog-label">Deskripsi</label><textarea id="milestone-description" wire:model="milestoneDescription" rows="2" class="worklog-input"></textarea></div>
                    <div class="flex justify-end gap-2"><button type="button" wire:click="$set('showMilestoneForm', false)" class="ui-button-secondary">Batal</button><button type="submit" class="ui-button-primary">Simpan Milestone</button></div>
                </form>
            @endif

            <div class="mt-5 space-y-3">
                @forelse ($project->milestones as $milestone)
                    @php($milestoneTone = match ($milestone->status->value) { 'completed' => 'bg-emerald-100 text-emerald-700', 'in_progress' => 'bg-blue-100 text-blue-700', default => 'bg-slate-100 text-slate-600' })
                    <div class="rounded-xl border border-slate-200 p-4"><div class="flex items-start gap-3"><span class="mt-1 h-3 w-3 shrink-0 rounded-full {{ $milestone->status->value === 'completed' ? 'bg-emerald-500' : ($milestone->status->value === 'in_progress' ? 'bg-brand' : 'bg-slate-300') }}"></span><div class="min-w-0 flex-1"><div class="flex flex-wrap items-start justify-between gap-2"><div><h3 class="text-sm font-bold text-slate-900">{{ $milestone->title }}</h3><p class="mt-1 text-xs text-slate-500">{{ $milestone->tasks_count }} task</p></div><span class="ui-badge {{ $milestoneTone }}">{{ $milestone->status->label() }}</span></div>@if ($milestone->description)<p class="mt-2 text-sm leading-6 text-slate-600">{{ $milestone->description }}</p>@endif<p class="mt-2 text-xs font-semibold text-slate-500">{{ $milestone->due_date ? 'Deadline '.$milestone->due_date->translatedFormat('d M Y') : 'Tanpa deadline' }}</p>@can('update', $project)<div class="mt-3 flex gap-3"><button type="button" wire:click="editMilestone({{ $milestone->id }})" class="text-xs font-bold text-brand">Edit</button><button type="button" wire:click="deleteMilestone({{ $milestone->id }})" class="text-xs font-bold text-rose-600">Hapus</button></div>@endcan</div></div></div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center"><p class="text-sm font-bold text-slate-700">Belum ada milestone</p><p class="mt-1 text-xs text-slate-500">Tambahkan milestone untuk membagi target project.</p></div>
                @endforelse
            </div>
        </section>

        <section class="ui-card p-5">
            <div class="flex items-end justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Kolaborasi</p><h2 class="mt-1 text-lg font-bold text-slate-900">Anggota</h2></div></div>
            <div class="mt-5 space-y-3">
                @foreach ($project->members as $member)
                    <div class="flex items-center gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-soft text-sm font-extrabold text-brand">{{ strtoupper(substr($member->name, 0, 1)) }}</span><div class="min-w-0 flex-1"><p class="truncate text-sm font-bold text-slate-900">{{ $member->name }}</p><p class="truncate text-xs text-slate-500">{{ $member->id === $project->owner_id ? 'Owner' : ($member->position ?: 'Anggota') }}</p></div>@can('update', $project) @if ($member->id !== $project->owner_id)<button type="button" wire:click="removeMember({{ $member->id }})" class="text-xs font-bold text-rose-600">Hapus</button>@endif @endcan</div>
                @endforeach
            </div>
            @can('update', $project)
                @php($memberIds = $project->members->pluck('id')->all())
                <div class="mt-5 border-t border-slate-100 pt-4"><label for="add-member" class="worklog-label">Tambah anggota</label><select id="add-member" x-data x-on:change="$wire.addMember($event.target.value); $event.target.value = ''" class="worklog-input"><option value="">Pilih anggota</option>@foreach ($users as $user) @if (!in_array($user->id, $memberIds, true))<option value="{{ $user->id }}">{{ $user->name }}</option>@endif @endforeach</select></div>
            @endcan
        </section>
    </div>
</div>
