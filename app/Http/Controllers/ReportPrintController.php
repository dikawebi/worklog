<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\WorkLogReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportPrintController extends Controller
{
    public function __invoke(Request $request, WorkLogReport $report): View
    {
        $filters = $report->filters($request->only(['from', 'to', 'user', 'project', 'category']));
        $viewer = $request->user();

        return view('reports.print', [
            'filters' => $filters,
            'summary' => $report->summary($viewer, $filters),
            'perUser' => $report->perUser($viewer, $filters),
            'perProject' => $report->perProject($viewer, $filters),
            'entries' => $report->detailQuery($viewer, $filters)->get(),
        ]);
    }
}
