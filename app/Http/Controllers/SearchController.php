<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkLogEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $viewer = $request->user();
        $canViewAll = $viewer->isManager() || $viewer->isAdmin();
        $results = [
            'worklogs' => collect(),
            'projects' => collect(),
            'tasks' => collect(),
            'users' => collect(),
        ];

        if (mb_strlen($query) >= 2) {
            $like = '%'.$query.'%';

            $results['worklogs'] = WorkLogEntry::query()
                ->with(['dailyReport:id,user_id,report_date', 'dailyReport.user:id,name', 'project:id,code,name'])
                ->whereHas('dailyReport', function ($builder) use ($viewer, $canViewAll): void {
                    if (! $canViewAll) {
                        $builder->where('user_id', $viewer->id);
                    }
                })
                ->where('description', 'like', $like)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            $results['projects'] = Project::query()
                ->when(! $canViewAll, fn ($builder) => $builder->where(function ($builder) use ($viewer): void {
                    $builder->where('owner_id', $viewer->id)
                        ->orWhereHas('members', fn ($members) => $members->whereKey($viewer->id));
                }))
                ->where(function ($builder) use ($like): void {
                    $builder->where('code', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('description', 'like', $like);
                })
                ->orderBy('code')
                ->limit(10)
                ->get();

            $results['tasks'] = Task::query()
                ->with(['project:id,code,name', 'assignee:id,name'])
                ->when(! $canViewAll, fn ($builder) => $builder->where('assignee_id', $viewer->id))
                ->where(function ($builder) use ($like): void {
                    $builder->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like);
                })
                ->orderByRaw("CASE status WHEN 'todo' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'blocked' THEN 3 ELSE 4 END")
                ->orderBy('due_date')
                ->limit(10)
                ->get();

            if ($canViewAll) {
                $results['users'] = User::query()
                    ->where('is_active', true)
                    ->where(function ($builder) use ($like): void {
                        $builder->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('position', 'like', $like);
                    })
                    ->orderBy('name')
                    ->limit(10)
                    ->get();
            }
        }

        return view('search.index', [
            'query' => $query,
            'results' => $results,
            'total' => collect($results)->sum(fn ($items): int => $items->count()),
        ]);
    }
}
