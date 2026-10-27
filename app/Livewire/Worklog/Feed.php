<?php

declare(strict_types=1);

namespace App\Livewire\Worklog;

use App\Models\DailyReport;
use App\Models\Project;
use App\Models\User;
use Carbon\CarbonImmutable;
use Livewire\Component;
use Livewire\WithPagination;

class Feed extends Component
{
    use WithPagination;

    public string $period = 'today';

    public string $from = '';

    public string $to = '';

    public string $userFilter = '';

    public string $projectFilter = '';

    public function mount(): void
    {
        $this->from = today()->format('Y-m-d');
        $this->to = today()->format('Y-m-d');
    }

    public function setPeriod(string $period): void
    {
        if (! in_array($period, ['today', 'week', 'range'], true)) {
            return;
        }

        $this->period = $period;

        if ($period === 'today') {
            $this->from = today()->format('Y-m-d');
            $this->to = $this->from;
        }

        if ($period === 'week') {
            $this->from = today()->startOfWeek()->format('Y-m-d');
            $this->to = today()->format('Y-m-d');
        }

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

    public function updatedFrom(): void
    {
        $this->period = 'range';
        $this->resetPage();
    }

    public function updatedTo(): void
    {
        $this->period = 'range';
        $this->resetPage();
    }

    public function render()
    {
        [$from, $to] = $this->dateRange();

        $reports = DailyReport::query()
            ->with([
                'user:id,name,position',
                'entries' => fn ($query) => $query
                    ->with([
                        'project:id,code,name',
                        'category:id,name,color',
                    ])
                    ->withCount('attachments')
                    ->orderBy('sort_order'),
            ])
            ->withCount('entries')
            ->whereDate('report_date', '>=', $from->toDateString())
            ->whereDate('report_date', '<=', $to->toDateString())
            ->whereHas('entries')
            ->when($this->userFilter, fn ($query) => $query->where('user_id', $this->userFilter))
            ->when($this->projectFilter, fn ($query) => $query->whereHas(
                'entries',
                fn ($entryQuery) => $entryQuery->where('project_id', $this->projectFilter),
            ))
            ->orderByDesc('report_date')
            ->orderByDesc('last_edited_at')
            ->paginate(10);

        return view('livewire.worklog.feed', [
            'reports' => $reports,
            'users' => User::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'projects' => Project::query()
                ->orderBy('code')
                ->get(['id', 'code', 'name']),
            'rangeLabel' => $this->rangeLabel($from, $to),
        ]);
    }

    private function dateRange(): array
    {
        $today = today()->toImmutable();

        if ($this->period === 'today') {
            return [$today, $today];
        }

        if ($this->period === 'week') {
            return [$today->startOfWeek(), $today];
        }

        $from = $this->parseDate($this->from) ?? $today;
        $to = $this->parseDate($this->to) ?? $from;

        return $from->greaterThan($to) ? [$to, $from] : [$from, $to];
    }

    private function parseDate(string $date): ?CarbonImmutable
    {
        try {
            $parsed = CarbonImmutable::createFromFormat('!Y-m-d', $date);
        } catch (\Throwable) {
            return null;
        }

        return $parsed && $parsed->format('Y-m-d') === $date ? $parsed : null;
    }

    private function rangeLabel(CarbonImmutable $from, CarbonImmutable $to): string
    {
        if ($from->isSameDay($to)) {
            return $from->translatedFormat('d F Y');
        }

        return $from->translatedFormat('d M Y').' — '.$to->translatedFormat('d M Y');
    }
}
