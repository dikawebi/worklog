<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DailyReport;
use Illuminate\View\View;

class WorklogShowController extends Controller
{
    public function __invoke(DailyReport $report): View
    {
        $report->load([
            'user:id,name,position',
            'entries' => fn ($query) => $query
                ->with([
                    'project:id,code,name',
                    'task:id,title',
                    'category:id,name,color',
                    'attachments',
                ])
                ->orderBy('sort_order'),
        ]);

        abort_if($report->entries->isEmpty(), 404);

        return view('worklogs.show', compact('report'));
    }
}
