<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_manage_settings(): void
    {
        $member = User::factory()->create();

        Livewire::actingAs($member)
            ->test('admin.settings')
            ->assertForbidden();
    }

    public function test_admin_can_update_settings(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('admin.settings')
            ->set('appName', 'IT Worklog BP')
            ->set('timezone', 'Asia/Makassar')
            ->set('workdays', [1, 2, 3, 4, 5])
            ->set('reminderTime', '16:30')
            ->set('mailFromAddress', 'admin.it@borneoprima.com')
            ->set('mailFromName', 'IT Worklog')
            ->call('save')
            ->assertHasNoErrors();

        $settings = Setting::current();

        $this->assertSame('IT Worklog BP', $settings->app_name);
        $this->assertSame('Asia/Makassar', $settings->timezone);
        $this->assertSame([1, 2, 3, 4, 5], $settings->workdays);
        $this->assertSame('16:30:00', $settings->reminder_time);
        $this->assertSame('admin.it@borneoprima.com', $settings->mail_from_address);
        $this->assertSame('Asia/Makassar', config('app.timezone'));
    }

    public function test_timezone_must_be_valid(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('admin.settings')
            ->set('timezone', 'Bukan/Timezone')
            ->call('save')
            ->assertHasErrors(['timezone']);
    }

    public function test_at_least_one_workday_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('admin.settings')
            ->set('workdays', [])
            ->call('save')
            ->assertHasErrors(['workdays']);
    }

    public function test_admin_can_enable_scheduled_reports(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('admin.settings')
            ->set('scheduledReportEnabled', true)
            ->set('scheduledReportFrequency', 'monthly')
            ->set('scheduledReportTime', '09:15')
            ->call('save')
            ->assertHasNoErrors();

        $settings = Setting::current();

        $this->assertTrue($settings->scheduled_report_enabled);
        $this->assertSame('monthly', $settings->scheduled_report_frequency);
        $this->assertSame('09:15:00', $settings->scheduled_report_time);
    }

    public function test_scheduled_report_frequency_must_be_supported(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('admin.settings')
            ->set('scheduledReportFrequency', 'hourly')
            ->call('save')
            ->assertHasErrors(['scheduledReportFrequency']);
    }
}
