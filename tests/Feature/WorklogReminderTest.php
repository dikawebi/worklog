<?php

namespace Tests\Feature;

use App\Enums\ReminderStatus;
use App\Mail\WorklogReminder;
use App\Models\DailyReport;
use App\Models\ReminderLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WorklogReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_reminder_is_sent_only_to_active_users_without_a_worklog(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-10-27 17:00:00', 'Asia/Jakarta'));
        $this->settings();

        $pending = User::factory()->create(['email' => 'pending@borneoprima.com']);
        $filled = User::factory()->create(['email' => 'filled@borneoprima.com']);
        $inactive = User::factory()->inactive()->create(['email' => 'inactive@borneoprima.com']);

        DailyReport::query()->create([
            'user_id' => $filled->id,
            'report_date' => today()->format('Y-m-d'),
            'first_submitted_at' => now(),
        ]);

        $this->artisan('worklog:send-reminders')->assertSuccessful();

        Mail::assertSentCount(1);
        Mail::assertSent(WorklogReminder::class, fn (WorklogReminder $mail): bool => $mail->hasTo($pending->email));
        Mail::assertNotSent(WorklogReminder::class, fn (WorklogReminder $mail): bool => $mail->hasTo($filled->email));
        Mail::assertNotSent(WorklogReminder::class, fn (WorklogReminder $mail): bool => $mail->hasTo($inactive->email));

        $this->assertDatabaseHas('reminder_logs', [
            'user_id' => $pending->id,
            'status' => ReminderStatus::Sent->value,
        ]);
        $this->assertDatabaseMissing('reminder_logs', ['user_id' => $filled->id]);
        $this->assertDatabaseMissing('reminder_logs', ['user_id' => $inactive->id]);
    }

    public function test_reminder_is_not_sent_twice_on_the_same_day(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-10-27 17:00:00', 'Asia/Jakarta'));
        $this->settings();
        User::factory()->create(['email' => 'pending@borneoprima.com']);

        $this->artisan('worklog:send-reminders')->assertSuccessful();
        $this->artisan('worklog:send-reminders')->assertSuccessful();

        Mail::assertSentCount(1);
        $this->assertSame(1, ReminderLog::query()->count());
    }

    public function test_reminder_is_not_sent_on_a_non_working_day(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-11-01 17:00:00', 'Asia/Jakarta'));
        $this->settings();
        User::factory()->create(['email' => 'pending@borneoprima.com']);

        $this->artisan('worklog:send-reminders')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertSame(0, ReminderLog::query()->count());
    }

    public function test_reminder_is_not_sent_before_the_configured_time(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-10-27 16:59:00', 'Asia/Jakarta'));
        $this->settings();
        User::factory()->create(['email' => 'pending@borneoprima.com']);

        $this->artisan('worklog:send-reminders')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertSame(0, ReminderLog::query()->count());
    }

    private function settings(): void
    {
        Setting::current()->update([
            'timezone' => 'Asia/Jakarta',
            'workdays' => [1, 2, 3, 4, 5, 6],
            'reminder_time' => '17:00:00',
            'mail_from_address' => 'admin.it@borneoprima.com',
            'mail_from_name' => 'IT Worklog',
        ]);
    }
}
