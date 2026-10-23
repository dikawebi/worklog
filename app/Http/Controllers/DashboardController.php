<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $today = today();

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
        ]);
    }
}
