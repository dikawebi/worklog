<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkLogEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $today = today();
        $weekStart = $today->copy()->startOfWeek();
        $canViewTeam = $user->isManager() || $user->isAdmin();

        $pendingTodayUsers = $canViewTeam
            ? User::query()
                ->where('is_active', true)
                ->whereDoesntHave('dailyReports', fn ($query) => $query->whereDate('report_date', $today))
                ->orderBy('name')
                ->get(['id', 'name', 'position'])
            : collect();

        $weeklyActivity = collect();

        if ($canViewTeam) {
            $activity = DailyReport::query()
                ->join('work_log_entries', 'work_log_entries.daily_report_id', '=', 'daily_reports.id')
                ->whereNull('work_log_entries.deleted_at')
                ->whereDate('daily_reports.report_date', '>=', $weekStart->toDateString())
                ->whereDate('daily_reports.report_date', '<=', $today->toDateString())
                ->selectRaw('daily_reports.user_id as user_id, count(distinct daily_reports.id) as days, count(work_log_entries.id) as entries')
                ->groupBy('daily_reports.user_id')
                ->get()
                ->keyBy('user_id');

            $weeklyActivity = User::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'position'])
                ->map(fn (User $member): array => [
                    'name' => $member->name,
                    'position' => $member->position,
                    'days' => (int) ($activity->get($member->id)?->days ?? 0),
                    'entries' => (int) ($activity->get($member->id)?->entries ?? 0),
                ])
                ->sortByDesc('entries')
                ->values();
        }

        return view('dashboard', [
            'projectCount' => Project::query()
                ->where('status', ProjectStatus::Active->value)
                ->count(),
            'overdueTasks' => Task::query()->overdue()->count(),
            'todayReports' => DailyReport::query()
                ->whereDate('report_date', $today)
                ->count(),
            'activeUsers' => User::query()->where('is_active', true)->count(),
            'hasTodayReport' => DailyReport::query()
                ->where('user_id', $user->id)
                ->whereDate('report_date', $today)
                ->exists(),
            'myOpenTasks' => Task::query()
                ->with('project')
                ->where('assignee_id', $user->id)
                ->where('status', '!=', TaskStatus::Done->value)
                ->orderBy('due_date')
                ->limit(5)
                ->get(),
            'recentReports' => DailyReport::query()
                ->with('user')
                ->withCount('entries')
                ->latest('report_date')
                ->limit(5)
                ->get(),
            'activeProjects' => Project::query()
                ->where('status', ProjectStatus::Active->value)
                ->withCount([
                    'tasks',
                    'tasks as completed_tasks_count' => fn ($query) => $query
                        ->where('status', TaskStatus::Done->value),
                ])
                ->orderBy('due_date')
                ->limit(4)
                ->get(),
            'myWeekEntries' => WorkLogEntry::query()
                ->whereHas('dailyReport', fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->whereDate('report_date', '>=', $weekStart->toDateString())
                    ->whereDate('report_date', '<=', $today->toDateString()))
                ->count(),
            'myWeekDays' => DailyReport::query()
                ->where('user_id', $user->id)
                ->whereDate('report_date', '>=', $weekStart->toDateString())
                ->whereDate('report_date', '<=', $today->toDateString())
                ->count(),
            'myOpenTasksCount' => Task::query()
                ->where('assignee_id', $user->id)
                ->where('status', '!=', TaskStatus::Done->value)
                ->count(),
            'pendingTodayUsers' => $pendingTodayUsers,
            'weeklyActivity' => $weeklyActivity,
            'canViewTeam' => $canViewTeam,
        ]);
    }
}
