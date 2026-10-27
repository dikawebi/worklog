<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WorkLogEntry;
use Illuminate\Support\Collection;

class WorkLogReportCsv
{
    /**
     * @param  Collection<int, WorkLogEntry>  $entries
     */
    public function make(Collection $entries): string
    {
        $output = fopen('php://temp', 'r+');

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

        rewind($output);
        $csv = stream_get_contents($output);

        fclose($output);

        return $csv === false ? '' : $csv;
    }
}
