<?php

declare(strict_types=1);

namespace App\Livewire\Tasks;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $viewMode = 'board';

    public string $search = '';

    public string $statusFilter = '';

    public string $priorityFilter = '';

    public string $assigneeFilter = '';

    public string $projectFilter = '';

    public bool $overdueOnly = false;

    public bool $showForm = false;

    public ?int $editingId = null;

    public ?int $projectId = null;

    public ?int $milestoneId = null;

    public ?int $assigneeId = null;

    public string $title = '';

    public ?string $description = null;

    public string $priority = 'normal';

    public ?string $dueDate = null;

    public ?string $successMessage = null;

    public function mount(): void
    {
        Gate::authorize('viewAny', Task::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAssigneeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedProjectFilter(): void
    {
        $this->resetPage();
    }

    public function updatedOverdueOnly(): void
    {
        $this->resetPage();
    }

    public function updatedProjectId(): void
    {
        if (! $this->milestoneId) {
            return;
        }

        $belongsToProject = Milestone::query()
            ->whereKey($this->milestoneId)
            ->where('project_id', $this->projectId)
            ->exists();

        if (! $belongsToProject) {
            $this->milestoneId = null;
        }
    }

    public function create(): void
    {
        Gate::authorize('create', Task::class);

        $this->resetForm();
        $this->showForm = true;
        $this->successMessage = null;
    }

    public function edit(int $taskId): void
    {
        $task = Task::query()->findOrFail($taskId);
        Gate::authorize('update', $task);

        $this->editingId = $task->id;
        $this->projectId = $task->project_id;
        $this->milestoneId = $task->milestone_id;
        $this->assigneeId = $task->assignee_id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->priority = $task->priority->value;
        $this->dueDate = $task->due_date?->format('Y-m-d');
        $this->showForm = true;
        $this->successMessage = null;
        $this->resetValidation();
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function save(): void
    {
        $task = $this->editingId
            ? Task::query()->findOrFail($this->editingId)
            : new Task;

        Gate::authorize($this->editingId ? 'update' : 'create', $task);

        $this->validate([
            'projectId' => ['required', 'integer', Rule::exists('projects', 'id')->whereNull('deleted_at')],
            'milestoneId' => ['nullable', 'integer', Rule::exists('milestones', 'id')->whereNull('deleted_at')],
            'assigneeId' => ['nullable', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'dueDate' => ['nullable', 'date'],
        ], [
            'projectId.required' => 'Project wajib dipilih.',
            'title.required' => 'Judul task wajib diisi.',
        ]);

        if ($this->milestoneId) {
            $milestoneBelongsToProject = Milestone::query()
                ->whereKey($this->milestoneId)
                ->where('project_id', $this->projectId)
                ->exists();

            if (! $milestoneBelongsToProject) {
                $this->addError('milestoneId', 'Milestone tidak termasuk dalam project yang dipilih.');

                return;
            }
        }

        $previousAssignee = $task->exists ? $task->assignee_id : null;

        $task->fill([
            'project_id' => $this->projectId,
            'milestone_id' => $this->milestoneId ?: null,
            'assignee_id' => $this->assigneeId ?: null,
            'title' => $this->title,
            'description' => $this->description ?: null,
            'priority' => $this->priority,
            'due_date' => $this->dueDate ?: null,
        ]);

        if (! $task->exists) {
            $task->status = TaskStatus::Todo;
            $task->created_by_id = auth()->id();
        }

        $task->save();

        if ($task->assignee_id && $task->assignee_id !== $previousAssignee && $task->assignee_id !== auth()->id()) {
            $task->assignee?->notify(new TaskAssigned($task, auth()->user()));
        }

        $this->successMessage = $this->editingId
            ? 'Task berhasil diperbarui.'
            : 'Task baru berhasil dibuat.';
        $this->showForm = false;
        $this->resetForm();
    }

    public function changeStatus(int $taskId, string $status): void
    {
        $task = Task::query()->findOrFail($taskId);
        Gate::authorize('changeStatus', $task);

        $next = TaskStatus::tryFrom($status);

        if (! $next) {
            $this->addError('status', 'Status task tidak valid.');

            return;
        }

        if ($task->status === $next) {
            return;
        }

        if (! in_array($next->value, $this->allowedTransitions($task->status), true)) {
            $this->addError('status', 'Perubahan status tidak valid untuk tahap task ini.');

            return;
        }

        $changes = ['status' => $next];

        if ($next === TaskStatus::InProgress && ! $task->started_at) {
            $changes['started_at'] = now();
        }

        if ($next === TaskStatus::Done) {
            $changes['completed_at'] = now();
        }

        $task->update($changes);
        $this->successMessage = 'Status task diperbarui.';
    }

    public function delete(int $taskId): void
    {
        $task = Task::query()->findOrFail($taskId);
        Gate::authorize('delete', $task);

        $task->delete();
        $this->successMessage = 'Task berhasil dihapus.';
    }

    public function render()
    {
        $statuses = TaskStatus::cases();
        $query = $this->filteredQuery();
        $boardTasks = collect();

        if ($this->viewMode === 'board') {
            $all = (clone $query)
                ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END")
                ->orderByRaw('due_date IS NULL')
                ->orderBy('due_date')
                ->limit(300)
                ->get();

            $boardTasks = collect($statuses)->mapWithKeys(
                fn (TaskStatus $status): array => [
                    $status->value => $all->filter(fn (Task $task): bool => $task->status === $status)->values(),
                ],
            );
        }

        return view('livewire.tasks.index', [
            'tasks' => $this->viewMode === 'list' ? $query->paginate(20) : null,
            'boardTasks' => $boardTasks,
            'statuses' => $statuses,
            'priorities' => TaskPriority::cases(),
            'projects' => Project::query()
                ->where(function (Builder $query): void {
                    $query->whereNotIn('status', [ProjectStatus::Completed->value, ProjectStatus::Cancelled->value]);

                    if ($this->projectId) {
                        $query->orWhere('id', $this->projectId);
                    }
                })
                ->orderBy('code')
                ->get(['id', 'code', 'name']),
            'milestones' => $this->projectId
                ? Milestone::query()
                    ->where('project_id', $this->projectId)
                    ->orderBy('sort_order')
                    ->orderBy('due_date')
                    ->get(['id', 'title'])
                : collect(),
            'users' => User::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'canCreate' => auth()->user()->isManager() || auth()->user()->isAdmin(),
        ]);
    }

    private function filteredQuery(): Builder
    {
        return Task::query()
            ->with([
                'project:id,code,name',
                'milestone:id,title',
                'assignee:id,name',
            ])
            ->when(! auth()->user()->isManager() && ! auth()->user()->isAdmin(), function (Builder $query): void {
                $query->where('assignee_id', auth()->id());
            })
            ->when($this->search, fn (Builder $query) => $query->where(function (Builder $query): void {
                $query->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            }))
            ->when($this->statusFilter, fn (Builder $query) => $query->where('status', $this->statusFilter))
            ->when($this->priorityFilter, fn (Builder $query) => $query->where('priority', $this->priorityFilter))
            ->when($this->assigneeFilter, fn (Builder $query) => $query->where('assignee_id', $this->assigneeFilter))
            ->when($this->projectFilter, fn (Builder $query) => $query->where('project_id', $this->projectFilter))
            ->when($this->overdueOnly, fn (Builder $query) => $query->whereDate('due_date', '<', today())->where('status', '!=', TaskStatus::Done->value))
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END")
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->orderBy('title');
    }

    /**
     * @return array<int, string>
     */
    private function allowedTransitions(TaskStatus $status): array
    {
        return match ($status) {
            TaskStatus::Todo => [TaskStatus::InProgress->value],
            TaskStatus::InProgress => [TaskStatus::Blocked->value, TaskStatus::Done->value],
            TaskStatus::Blocked => [TaskStatus::InProgress->value],
            TaskStatus::Done => [],
        };
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'projectId',
            'milestoneId',
            'assigneeId',
            'title',
            'description',
            'dueDate',
        ]);
        $this->priority = TaskPriority::Normal->value;
        $this->resetValidation();
    }
}
