<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class AuditLog extends Component
{
    use WithPagination;

    public string $search = '';

    public string $eventFilter = '';

    public string $userFilter = '';

    public string $from = '';

    public string $to = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEventFilter(): void
    {
        $this->resetPage();
    }

    public function updatedUserFilter(): void
    {
        $this->resetPage();
    }

    public function updatedFrom(): void
    {
        $this->resetPage();
    }

    public function updatedTo(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $activities = Activity::query()
            ->with('causer:id,name,email')
            ->when($this->search, fn ($query) => $query->where(function ($query): void {
                $query->where('description', 'like', '%'.$this->search.'%')
                    ->orWhere('subject_type', 'like', '%'.$this->search.'%')
                    ->orWhereHasMorph('causer', [User::class], fn ($query) => $query
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%'));
            }))
            ->when($this->eventFilter, fn ($query) => $query->where('event', $this->eventFilter))
            ->when($this->userFilter, fn ($query) => $query->where('causer_id', $this->userFilter)->where('causer_type', User::class))
            ->when($this->from, fn ($query) => $query->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($query) => $query->whereDate('created_at', '<=', $this->to))
            ->latest()
            ->paginate(25);

        return view('livewire.admin.audit-log', [
            'activities' => $activities,
            'events' => ['created', 'updated', 'deleted', 'restored'],
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }
}
