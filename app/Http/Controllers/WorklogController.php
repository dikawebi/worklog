<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WorklogController extends Controller
{
    public function __invoke(Request $request, ?string $date = null): View
    {
        $selectedDate = $date
            ? CarbonImmutable::createFromFormat('!Y-m-d', $date)
            : today()->toImmutable();

        abort_if($selectedDate === false, 404);

        $prefillTaskId = null;
        $taskId = $request->integer('task');

        if ($taskId > 0 && ($task = Task::query()->find($taskId))) {
            Gate::authorize('view', $task);
            $prefillTaskId = $task->id;
        }

        return view('worklogs.index', [
            'selectedDate' => $selectedDate,
            'prefillTaskId' => $prefillTaskId,
        ]);
    }
}
