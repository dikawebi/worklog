<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Services\WorkLogReport;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $from = '';

    public string $to = '';

    public string $userFilter = '';

    public string $projectFilter = '';

    public string $categoryFilter = '';

    public string $period = 'month';

    public function mount(): void
    {
        $this->from = today()->startOfMonth()->toDateString();
        $this->to = today()->toDateString();
    }

    public function updatedFrom(): void
    {
        $this->period = 'custom';
        $this->resetPage();
    }

    public function updatedTo(): void
    {
        $this->period = 'custom';
        $this->resetPage();
    }

    public function updatedUserFilter(): void
    {
        $this->resetPage();
    }

    public function updatedProjectFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['userFilter', 'projectFilter', 'categoryFilter']);
        $this->from = today()->startOfMonth()->toDateString();
        $this->to = today()->toDateString();
        $this->period = 'month';
        $this->resetPage();
    }

    public function setPeriod(string $period): void
    {
        $today = today()->toImmutable();

        if ($period === 'today') {
            $this->from = $today->toDateString();
            $this->to = $this->from;
        } elseif ($period === 'week') {
            $this->from = $today->startOfWeek()->toDateString();
            $this->to = $today->toDateString();
        } elseif ($period === 'month') {
            $this->from = $today->startOfMonth()->toDateString();
            $this->to = $today->toDateString();
        } else {
            return;
        }

        $this->period = $period;
        $this->resetPage();
    }

    public function render()
    {
        $service = app(WorkLogReport::class);
        $viewer = auth()->user();
        $filters = $service->filters([
            'from' => $this->from,
            'to' => $this->to,
            'user' => $this->userFilter,
            'project' => $this->projectFilter,
            'category' => $this->categoryFilter,
        ]);

        $entries = $service->detailQuery($viewer, $filters)
            ->paginate(20)
            ->withQueryString();

        return view('livewire.reports.index', [
            'filters' => $filters,
            'summary' => $service->summary($viewer, $filters),
            'perUser' => $service->perUser($viewer, $filters),
            'perProject' => $service->perProject($viewer, $filters),
            'entries' => $entries,
            'users' => $service->users($viewer),
            'projects' => $service->projects(),
            'categories' => $service->categories(),
            'canViewAll' => $service->canViewAll($viewer),
            'exportUrl' => route('reports.export', array_filter($filters)),
            'printUrl' => route('reports.print', array_filter($filters)),
        ]);
    }
}
