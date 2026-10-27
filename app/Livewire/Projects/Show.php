<?php

declare(strict_types=1);

namespace App\Livewire\Projects;

use App\Enums\MilestoneStatus;
use App\Enums\ProjectStatus;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Show extends Component
{
    public Project $project;

    public bool $editing = false;

    public string $name = '';

    public ?string $description = null;

    public string $status = 'planning';

    public ?int $ownerId = null;

    public ?string $startDate = null;

    public ?string $dueDate = null;

    public bool $showMilestoneForm = false;

    public ?int $editingMilestoneId = null;

    public string $milestoneTitle = '';

    public ?string $milestoneDescription = null;

    public string $milestoneStatus = 'pending';

    public ?string $milestoneDueDate = null;

    public ?string $successMessage = null;

    public function mount(Project $project): void
    {
        Gate::authorize('view', $project);
        $this->project = $project;
        $this->fillProjectForm();
    }

    public function startEditing(): void
    {
        Gate::authorize('update', $this->project);
        $this->editing = true;
    }

    public function cancelEditing(): void
    {
        $this->fillProjectForm();
        $this->editing = false;
        $this->resetValidation();
    }

    public function saveProject(): void
    {
        Gate::authorize('update', $this->project);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'ownerId' => ['required', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
            'startDate' => ['nullable', 'date'],
            'dueDate' => ['nullable', 'date', 'after_or_equal:startDate'],
        ]);

        $this->project->update([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'status' => $this->status,
            'owner_id' => $this->ownerId,
            'start_date' => $this->startDate ?: null,
            'due_date' => $this->dueDate ?: null,
        ]);
        $this->project->members()->syncWithoutDetaching([$this->ownerId => ['role_in_project' => 'owner']]);
        $this->project->refresh();
        $this->fillProjectForm();
        $this->editing = false;
        $this->successMessage = 'Project berhasil diperbarui.';
    }

    public function addMember(int $userId): void
    {
        Gate::authorize('update', $this->project);

        $this->project->members()->syncWithoutDetaching([$userId => ['role_in_project' => 'member']]);
        $this->project->load('members');
        $this->successMessage = 'Anggota berhasil ditambahkan.';
    }

    public function removeMember(int $userId): void
    {
        Gate::authorize('update', $this->project);

        abort_if($userId === $this->project->owner_id, 422, 'Owner harus dipindahkan sebelum dikeluarkan.');
        $this->project->members()->detach($userId);
        $this->project->load('members');
        $this->successMessage = 'Anggota berhasil dikeluarkan.';
    }

    public function startMilestoneForm(): void
    {
        Gate::authorize('update', $this->project);
        $this->resetMilestoneForm();
        $this->showMilestoneForm = true;
    }

    public function editMilestone(int $milestoneId): void
    {
        Gate::authorize('update', $this->project);
        $milestone = $this->project->milestones()->findOrFail($milestoneId);
        $this->editingMilestoneId = $milestone->id;
        $this->milestoneTitle = $milestone->title;
        $this->milestoneDescription = $milestone->description;
        $this->milestoneStatus = $milestone->status->value;
        $this->milestoneDueDate = $milestone->due_date?->format('Y-m-d');
        $this->showMilestoneForm = true;
    }

    public function saveMilestone(): void
    {
        Gate::authorize('update', $this->project);

        $this->validate([
            'milestoneTitle' => ['required', 'string', 'max:255'],
            'milestoneDescription' => ['nullable', 'string', 'max:5000'],
            'milestoneStatus' => ['required', Rule::enum(MilestoneStatus::class)],
            'milestoneDueDate' => ['nullable', 'date'],
        ]);

        $milestone = $this->editingMilestoneId
            ? $this->project->milestones()->findOrFail($this->editingMilestoneId)
            : new Milestone(['project_id' => $this->project->id, 'sort_order' => $this->project->milestones()->count()]);

        $milestone->fill([
            'title' => $this->milestoneTitle,
            'description' => $this->milestoneDescription ?: null,
            'status' => $this->milestoneStatus,
            'due_date' => $this->milestoneDueDate ?: null,
            'completed_at' => $this->milestoneStatus === MilestoneStatus::Completed->value ? ($milestone->completed_at ?? now()) : null,
        ])->save();

        $this->resetMilestoneForm();
        $this->showMilestoneForm = false;
        $this->successMessage = 'Milestone berhasil disimpan.';
    }

    public function deleteMilestone(int $milestoneId): void
    {
        Gate::authorize('update', $this->project);
        $this->project->milestones()->findOrFail($milestoneId)->delete();
        $this->successMessage = 'Milestone berhasil dihapus.';
    }

    public function render()
    {
        $this->project->load([
            'owner:id,name,position',
            'members:id,name,position',
            'milestones' => fn ($query) => $query->withCount('tasks')->orderBy('sort_order')->orderBy('due_date'),
        ]);
        $this->project->loadCount([
            'tasks',
            'milestones',
            'workLogEntries',
            'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
        ]);

        return view('livewire.projects.show', [
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'position']),
            'statuses' => ProjectStatus::cases(),
            'milestoneStatuses' => MilestoneStatus::cases(),
        ]);
    }

    private function fillProjectForm(): void
    {
        $this->name = $this->project->name;
        $this->description = $this->project->description;
        $this->status = $this->project->status->value;
        $this->ownerId = $this->project->owner_id;
        $this->startDate = $this->project->start_date?->format('Y-m-d');
        $this->dueDate = $this->project->due_date?->format('Y-m-d');
    }

    private function resetMilestoneForm(): void
    {
        $this->reset(['editingMilestoneId', 'milestoneTitle', 'milestoneDescription', 'milestoneDueDate']);
        $this->milestoneStatus = MilestoneStatus::Pending->value;
        $this->resetValidation();
    }
}
