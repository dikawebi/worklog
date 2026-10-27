<?php

namespace Tests\Feature;

use App\Mail\ScheduledWorklogReport;
use App\Models\DailyReport;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ScheduledReportTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_weekly_report_is_sent_to_active_managers_and_admins(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-10-26 08:00:00', 'Asia/Jakarta'));
        $this->settings();

        $manager = User::factory()->manager()->create(['email' => 'manager@borneoprima.com']);
        $admin = User::factory()->admin()->create(['email' => 'admin@borneoprima.com']);
        $member = User::factory()->create(['email' => 'member@borneoprima.com']);
        User::factory()->manager()->inactive()->create(['email' => 'inactive@borneoprima.com']);

        DailyReport::query()->create([
            'user_id' => $member->id,
            'report_date' => now()->startOfWeek()->subWeek()->addDay()->toDateString(),
            'first_submitted_at' => now(),
        ])->entries()->create([
            'description' => 'Pekerjaan minggu lalu.',
            'sort_order' => 0,
        ]);

        $this->artisan('reports:send-scheduled')->assertSuccessful();

        Mail::assertSentCount(2);
        Mail::assertSent(ScheduledWorklogReport::class, fn (ScheduledWorklogReport $mail): bool => $mail->hasTo($manager->email));
        Mail::assertSent(ScheduledWorklogReport::class, fn (ScheduledWorklogReport $mail): bool => $mail->hasTo($admin->email));
        Mail::assertNotSent(ScheduledWorklogReport::class, fn (ScheduledWorklogReport $mail): bool => $mail->hasTo($member->email));
        Mail::assertNotSent(ScheduledWorklogReport::class, fn (ScheduledWorklogReport $mail): bool => $mail->hasTo('inactive@borneoprima.com'));

        Mail::assertSent(ScheduledWorklogReport::class, fn (ScheduledWorklogReport $mail): bool => $mail->frequency === 'weekly'
            && $mail->summary['entries'] === 1
            && $mail->filters['from'] === now()->startOfWeek()->subWeek()->toDateString());

        $this->assertNotNull(Setting::current()->scheduled_report_last_sent_at);
    }

    public function test_scheduled_report_is_not_sent_twice_on_the_same_day(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-10-26 08:00:00', 'Asia/Jakarta'));
        $this->settings();
        User::factory()->manager()->create(['email' => 'manager@borneoprima.com']);

        $this->artisan('reports:send-scheduled')->assertSuccessful();
        $this->artisan('reports:send-scheduled')->assertSuccessful();

        Mail::assertSentCount(1);
    }

    public function test_scheduled_report_is_not_sent_when_disabled(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-10-26 08:00:00', 'Asia/Jakarta'));
        $this->settings(['scheduled_report_enabled' => false]);
        User::factory()->manager()->create(['email' => 'manager@borneoprima.com']);

        $this->artisan('reports:send-scheduled')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_scheduled_report_is_not_sent_before_the_configured_time(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-10-26 07:59:00', 'Asia/Jakarta'));
        $this->settings();
        User::factory()->manager()->create(['email' => 'manager@borneoprima.com']);

        $this->artisan('reports:send-scheduled')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_scheduled_report_is_not_sent_on_a_non_scheduled_day(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-10-27 08:00:00', 'Asia/Jakarta'));
        $this->settings();
        User::factory()->manager()->create(['email' => 'manager@borneoprima.com']);

        $this->artisan('reports:send-scheduled')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_daily_report_uses_yesterdays_period(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-10-27 08:00:00', 'Asia/Jakarta'));
        $this->settings(['scheduled_report_frequency' => 'daily']);
        User::factory()->manager()->create(['email' => 'manager@borneoprima.com']);

        $this->artisan('reports:send-scheduled')->assertSuccessful();

        Mail::assertSent(ScheduledWorklogReport::class, fn (ScheduledWorklogReport $mail): bool => $mail->frequency === 'daily'
            && $mail->filters['from'] === '2026-10-26'
            && $mail->filters['to'] === '2026-10-26');
    }

    public function test_monthly_report_uses_the_previous_month(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::parse('2026-11-01 08:00:00', 'Asia/Jakarta'));
        $this->settings(['scheduled_report_frequency' => 'monthly']);
        User::factory()->manager()->create(['email' => 'manager@borneoprima.com']);

        $this->artisan('reports:send-scheduled')->assertSuccessful();

        Mail::assertSent(ScheduledWorklogReport::class, fn (ScheduledWorklogReport $mail): bool => $mail->frequency === 'monthly'
            && $mail->filters['from'] === '2026-10-01'
            && $mail->filters['to'] === '2026-10-31');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function settings(array $overrides = []): void
    {
        Setting::current()->update(array_merge([
            'timezone' => 'Asia/Jakarta',
            'mail_from_address' => 'admin.it@borneoprima.com',
            'mail_from_name' => 'IT Worklog',
            'scheduled_report_enabled' => true,
            'scheduled_report_frequency' => 'weekly',
            'scheduled_report_time' => '08:00:00',
        ], $overrides));
    }
}
