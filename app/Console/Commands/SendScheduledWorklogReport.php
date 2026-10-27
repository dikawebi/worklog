<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Mail\ScheduledWorklogReport;
use App\Models\Setting;
use App\Models\User;
use App\Services\WorkLogReport;
use App\Services\WorkLogReportCsv;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendScheduledWorklogReport extends Command
{
    protected $signature = 'reports:send-scheduled {--force : Kirim sekarang tanpa menunggu jadwal}';

    protected $description = 'Kirim laporan worklog terjadwal kepada manager dan admin';

    public function handle(WorkLogReport $report, WorkLogReportCsv $csv): int
    {
        $settings = Setting::current();

        config([
            'app.timezone' => $settings->timezone,
            'mail.from.address' => $settings->mail_from_address ?: config('mail.from.address'),
            'mail.from.name' => $settings->mail_from_name ?: config('mail.from.name'),
        ]);
        date_default_timezone_set($settings->timezone);

        if (! $settings->scheduled_report_enabled && ! $this->option('force')) {
            $this->info('Laporan terjadwal sedang dinonaktifkan.');

            return self::SUCCESS;
        }

        $now = now();
        $frequency = in_array($settings->scheduled_report_frequency, ['daily', 'weekly', 'monthly'], true)
            ? $settings->scheduled_report_frequency
            : 'weekly';

        if (! $this->option('force')) {
            $sendTime = substr((string) $settings->scheduled_report_time, 0, 5);

            if ($now->format('H:i') < $sendTime) {
                $this->info('Belum waktunya mengirim laporan terjadwal.');

                return self::SUCCESS;
            }

            if (! $this->isDue($frequency, $now)) {
                $this->info('Laporan tidak dijadwalkan pada hari ini.');

                return self::SUCCESS;
            }

            if ($settings->scheduled_report_last_sent_at?->isSameDay($now)) {
                $this->info('Laporan hari ini sudah dikirim.');

                return self::SUCCESS;
            }
        }

        $recipients = User::query()
            ->where('is_active', true)
            ->whereIn('role', [UserRole::Manager->value, UserRole::Admin->value])
            ->orderBy('name')
            ->get();

        if ($recipients->isEmpty()) {
            $this->info('Tidak ada manager atau admin aktif sebagai penerima laporan.');

            return self::SUCCESS;
        }

        $anchor = $now->copy();
        [$from, $to] = $this->period($frequency, $anchor);
        $filters = $report->filters([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
        $viewer = $recipients->first();
        $summary = $report->summary($viewer, $filters);
        $perUser = $report->perUser($viewer, $filters);
        $perProject = $report->perProject($viewer, $filters);
        $entries = $report->detailQuery($viewer, $filters)->get();
        $csv = $csv->make($entries);

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient)->send(new ScheduledWorklogReport(
                    filters: $filters,
                    summary: $summary,
                    perUser: $perUser,
                    perProject: $perProject,
                    entries: $entries,
                    frequency: $frequency,
                    csv: $csv,
                ));
                $sent++;
            } catch (\Throwable $exception) {
                $failed++;
                $this->error('Gagal mengirim laporan ke '.$recipient->email.': '.$exception->getMessage());
            }
        }

        $settings->update(['scheduled_report_last_sent_at' => $now]);

        $this->info("Laporan worklog terkirim: {$sent}. Gagal: {$failed}. Periode: {$filters['from']} sampai {$filters['to']}.");

        return self::SUCCESS;
    }

    private function isDue(string $frequency, CarbonInterface $now): bool
    {
        return match ($frequency) {
            'daily' => true,
            'weekly' => $now->isMonday(),
            'monthly' => $now->day === 1,
            default => false,
        };
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    private function period(string $frequency, CarbonInterface $anchor): array
    {
        return match ($frequency) {
            'daily' => [
                $anchor->copy()->subDay()->startOfDay(),
                $anchor->copy()->subDay()->endOfDay(),
            ],
            'monthly' => [
                $anchor->copy()->subMonthNoOverflow()->startOfMonth()->startOfDay(),
                $anchor->copy()->subMonthNoOverflow()->endOfMonth()->endOfDay(),
            ],
            default => [
                $anchor->copy()->startOfWeek()->subWeek()->startOfDay(),
                $anchor->copy()->startOfWeek()->subWeek()->endOfWeek()->endOfDay(),
            ],
        };
    }
}
