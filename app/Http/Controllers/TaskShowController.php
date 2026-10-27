<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TaskShowController extends Controller
{
    public function __invoke(Task $task): View
    {
        Gate::authorize('view', $task);

        $task->load([
            'project:id,code,name',
            'milestone:id,title',
            'assignee:id,name,position',
            'creator:id,name',
        ]);

        return view('tasks.show', compact('task'));
    }
}
