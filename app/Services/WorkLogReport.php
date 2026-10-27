<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkLogEntry;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WorkLogReport
{
    /**
     * Normalize report filters into a predictable shape.
     *
     * @param  array<string, mixed>  $input
     * @return array{from: string, to: string, user: ?int, project: ?int, category: ?int}
     */
    public function filters(array $input): array
    {
        $today = today()->toImmutable();
        $from = $this->date($input['from'] ?? null) ?? $today->startOfMonth();
        $to = $this->date($input['to'] ?? null) ?? $today;

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'user' => $this->integer($input['user'] ?? null),
            'project' => $this->integer($input['project'] ?? null),
            'category' => $this->integer($input['category'] ?? null),
        ];
    }

    /**
     * @param  array{from: string, to: string, user: ?int, project: ?int, category: ?int}  $filters
     */
    public function query(User $viewer, array $filters): Builder
    {
        return WorkLogEntry::query()
            ->join('daily_reports', 'daily_reports.id', '=', 'work_log_entries.daily_report_id')
            ->join('users', 'users.id', '=', 'daily_reports.user_id')
            ->leftJoin('projects', 'projects.id', '=', 'work_log_entries.project_id')
            ->leftJoin('categories', 'categories.id', '=', 'work_log_entries.category_id')
            ->whereNull('work_log_entries.deleted_at')
            ->whereNull('daily_reports.deleted_at')
            ->when(! $this->canViewAll($viewer), fn (Builder $query) => $query->where('daily_reports.user_id', $viewer->id))
            ->whereDate('daily_reports.report_date', '>=', $filters['from'])
            ->whereDate('daily_reports.report_date', '<=', $filters['to'])
            ->when($filters['user'], fn (Builder $query) => $query->where('daily_reports.user_id', $filters['user']))
            ->when($filters['project'], fn (Builder $query) => $query->where('work_log_entries.project_id', $filters['project']))
            ->when($filters['category'], fn (Builder $query) => $query->where('work_log_entries.category_id', $filters['category']));
    }

    /**
     * @param  array{from: string, to: string, user: ?int, project: ?int, category: ?int}  $filters
     * @return array{entries: int, days: int, members: int}
     */
    public function summary(User $viewer, array $filters): array
    {
        $query = $this->query($viewer, $filters);

        return [
            'entries' => (clone $query)->count('work_log_entries.id'),
            'days' => (clone $query)->distinct()->count('daily_reports.report_date'),
            'members' => (clone $query)->distinct()->count('daily_reports.user_id'),
        ];
    }

    /**
     * @param  array{from: string, to: string, user: ?int, project: ?int, category: ?int}  $filters
     */
    public function detailQuery(User $viewer, array $filters): Builder
    {
        return $this->query($viewer, $filters)
            ->with([
                'dailyReport:id,user_id,report_date',
                'dailyReport.user:id,name,position',
                'project:id,code,name',
                'category:id,name,color',
            ])
            ->withCount('attachments')
            ->select('work_log_entries.*')
            ->orderByDesc('daily_reports.report_date')
            ->orderBy('work_log_entries.sort_order')
            ->orderBy('work_log_entries.id');
    }

    /**
     * @param  array{from: string, to: string, user: ?int, project: ?int, category: ?int}  $filters
     * @return Collection<int, object>
     */
    public function perUser(User $viewer, array $filters): Collection
    {
        return $this->query($viewer, $filters)
            ->selectRaw('daily_reports.user_id as user_id, users.name as name, users.position as position, count(work_log_entries.id) as entries, count(distinct daily_reports.report_date) as days')
            ->groupBy('daily_reports.user_id', 'users.name', 'users.position')
            ->orderByDesc('entries')
            ->orderBy('users.name')
            ->get();
    }

    /**
     * @param  array{from: string, to: string, user: ?int, project: ?int, category: ?int}  $filters
     * @return Collection<int, object>
     */
    public function perProject(User $viewer, array $filters): Collection
    {
        return $this->query($viewer, $filters)
            ->selectRaw('work_log_entries.project_id as project_id, projects.code as code, projects.name as name, count(work_log_entries.id) as entries')
            ->groupBy('work_log_entries.project_id', 'projects.code', 'projects.name')
            ->orderByDesc('entries')
            ->orderBy('projects.name')
            ->get();
    }

    public function canViewAll(User $viewer): bool
    {
        return $viewer->isManager() || $viewer->isAdmin();
    }

    /**
     * @return Collection<int, User>
     */
    public function users(User $viewer): Collection
    {
        return User::query()
            ->when(! $this->canViewAll($viewer), fn (Builder $query) => $query->whereKey($viewer->id))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'position']);
    }

    /**
     * @return Collection<int, Project>
     */
    public function projects(): Collection
    {
        return Project::query()->orderBy('code')->get(['id', 'code', 'name']);
    }

    /**
     * @return Collection<int, Category>
     */
    public function categories(): Collection
    {
        return Category::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
    }

    private function date(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }

        return $date && $date->format('Y-m-d') === $value ? $date : null;
    }

    private function integer(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT);

        return $integer === false ? null : $integer;
    }
}
