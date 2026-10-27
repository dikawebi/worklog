<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\WorkLogReport;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __invoke(Request $request, WorkLogReport $report): StreamedResponse
    {
        $filters = $report->filters($request->only(['from', 'to', 'user', 'project', 'category']));
        $entries = $report->detailQuery($request->user(), $filters)->get();
        $filename = 'worklog-'.$filters['from'].'-'.$filters['to'].'.csv';

        return response()->streamDownload(function () use ($entries): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Tanggal', 'Anggota', 'Jabatan', 'Project', 'Kategori', 'Pekerjaan', 'Lampiran']);

            foreach ($entries as $entry) {
                fputcsv($output, [
                    $entry->dailyReport?->report_date?->format('Y-m-d'),
                    $entry->dailyReport?->user?->name,
                    $entry->dailyReport?->user?->position,
                    $entry->project?->code,
                    $entry->category?->name,
                    $entry->description,
                    $entry->attachments_count,
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
