<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ReminderStatus;
use App\Mail\WorklogReminder;
use App\Models\ReminderLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendWorklogReminders extends Command
{
    protected $signature = 'worklog:send-reminders';

    protected $description = 'Kirim email pengingat worklog kepada anggota yang belum mengisi hari ini';

    public function handle(): int
    {
        $settings = Setting::current();

        config([
            'app.timezone' => $settings->timezone,
            'mail.from.address' => $settings->mail_from_address ?: config('mail.from.address'),
            'mail.from.name' => $settings->mail_from_name ?: config('mail.from.name'),
        ]);
        date_default_timezone_set($settings->timezone);

        $now = now();
        $workdays = array_map('intval', $settings->workdays ?? [1, 2, 3, 4, 5, 6]);

        if (! in_array($now->dayOfWeekIso, $workdays, true)) {
            $this->info('Hari ini bukan hari kerja. Pengingat dilewati.');

            return self::SUCCESS;
        }

        $reminderTime = substr((string) $settings->reminder_time, 0, 5);

        if ($now->format('H:i') < $reminderTime) {
            $this->info('Belum waktunya mengirim pengingat.');

            return self::SUCCESS;
        }

        $users = User::query()
            ->where('is_active', true)
            ->whereDoesntHave('dailyReports', fn ($query) => $query->whereDate('report_date', $now->toDateString()))
            ->whereDoesntHave('reminderLogs', fn ($query) => $query->whereDate('reminder_date', $now->toDateString()))
            ->orderBy('name')
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            $log = ReminderLog::query()->firstOrCreate(
                ['user_id' => $user->id, 'reminder_date' => $now->toDateString()],
                ['status' => ReminderStatus::Skipped],
            );

            if (! $log->wasRecentlyCreated) {
                continue;
            }

            try {
                Mail::to($user)->send(new WorklogReminder($user));
                $log->update([
                    'status' => ReminderStatus::Sent,
                    'sent_at' => now(),
                    'error' => null,
                ]);
                $sent++;
            } catch (\Throwable $exception) {
                $log->update([
                    'status' => ReminderStatus::Failed,
                    'error' => Str::limit($exception->getMessage(), 1000),
                ]);
                $failed++;
                $this->error('Gagal mengirim pengingat ke '.$user->email.': '.$exception->getMessage());
            }
        }

        $this->info("Pengingat terkirim: {$sent}. Gagal: {$failed}.");

        return self::SUCCESS;
    }
}
