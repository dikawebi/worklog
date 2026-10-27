<?php

declare(strict_types=1);

namespace App\Livewire\Projects;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $ownerFilter = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public ?string $description = null;

    public string $status = 'planning';

    public ?int $ownerId = null;

    public ?string $startDate = null;

    public ?string $dueDate = null;

    public ?string $successMessage = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedOwnerFilter(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        Gate::authorize('create', Project::class);

        $this->resetForm();
        $this->ownerId = auth()->id();
        $this->showForm = true;
    }

    public function edit(int $projectId): void
    {
        $project = Project::query()->findOrFail($projectId);
        Gate::authorize('update', $project);

        $this->editingId = $project->id;
        $this->code = $project->code;
        $this->name = $project->name;
        $this->description = $project->description;
        $this->status = $project->status->value;
        $this->ownerId = $project->owner_id;
        $this->startDate = $project->start_date?->format('Y-m-d');
        $this->dueDate = $project->due_date?->format('Y-m-d');
        $this->showForm = true;
        $this->successMessage = null;
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $project = $this->editingId ? Project::query()->findOrFail($this->editingId) : new Project;
        Gate::authorize($this->editingId ? 'update' : 'create', $project);

        $this->validate([
            'code' => ['required', 'string', 'max:32', 'regex:/^[A-Za-z0-9][A-Za-z0-9._-]*$/', Rule::unique('projects', 'code')->ignore($project->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'ownerId' => ['required', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
            'startDate' => ['nullable', 'date'],
            'dueDate' => ['nullable', 'date', 'after_or_equal:startDate'],
        ], [
            'code.regex' => 'Kode hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.',
            'dueDate.after_or_equal' => 'Deadline tidak boleh lebih awal dari tanggal mulai.',
        ]);

        $project->fill([
            'code' => strtoupper($this->code),
            'name' => $this->name,
            'description' => $this->description ?: null,
            'status' => $this->status,
            'owner_id' => $this->ownerId,
            'start_date' => $this->startDate ?: null,
            'due_date' => $this->dueDate ?: null,
            'completed_at' => $this->status === ProjectStatus::Completed->value ? ($project->completed_at ?? now()) : null,
        ])->save();

        $project->members()->syncWithoutDetaching([$this->ownerId => ['role_in_project' => 'owner']]);

        $this->showForm = false;
        $this->successMessage = $this->editingId ? 'Project berhasil diperbarui.' : 'Project berhasil dibuat.';
        $this->resetForm();
    }

    public function delete(int $projectId): void
    {
        $project = Project::query()->findOrFail($projectId);
        Gate::authorize('delete', $project);
        $project->delete();
        $this->successMessage = 'Project berhasil diarsipkan.';
    }

    public function render()
    {
        $query = Project::query()
            ->with('owner:id,name')
            ->withCount([
                'members',
                'tasks',
                'milestones',
                'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
            ])
            ->when(! auth()->user()->isManager() && ! auth()->user()->isAdmin(), function ($query) {
                $query->where(function ($query): void {
                    $query->where('owner_id', auth()->id())
                        ->orWhereHas('members', fn ($members) => $members->whereKey(auth()->id()));
                });
            })
            ->when($this->search, fn ($query) => $query->where(function ($query): void {
                $query->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%');
            }))
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->ownerFilter, fn ($query) => $query->where('owner_id', $this->ownerFilter))
            ->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'planning' THEN 2 WHEN 'on_hold' THEN 3 WHEN 'completed' THEN 4 WHEN 'cancelled' THEN 5 ELSE 6 END")
            ->orderBy('due_date')
            ->orderBy('name');

        return view('livewire.projects.index', [
            'projects' => $query->paginate(9),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'statuses' => ProjectStatus::cases(),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'description', 'status', 'ownerId', 'startDate', 'dueDate']);
        $this->status = ProjectStatus::Planning->value;
        $this->resetValidation();
    }
}
