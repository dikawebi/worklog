<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\WorkLogReport;
use App\Services\WorkLogReportCsv;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __invoke(Request $request, WorkLogReport $report, WorkLogReportCsv $csv): StreamedResponse
    {
        $filters = $report->filters($request->only(['from', 'to', 'user', 'project', 'category']));
        $entries = $report->detailQuery($request->user(), $filters)->get();
        $filename = 'worklog-'.$filters['from'].'-'.$filters['to'].'.csv';
        $content = $csv->make($entries);

        return response()->streamDownload(function () use ($content): void {
            echo $content;
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
