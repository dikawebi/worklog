<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TrashTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_access_data_restore(): void
    {
        $member = User::factory()->create();

        Livewire::actingAs($member)
            ->test('admin.trash')
            ->assertForbidden();
    }

    public function test_admin_can_restore_a_deleted_report_and_its_entries(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $report = DailyReport::query()->create([
            'user_id' => $user->id,
            'report_date' => today()->format('Y-m-d'),
            'first_submitted_at' => now(),
        ]);
        $entry = $report->entries()->create([
            'description' => 'Pekerjaan yang dipulihkan.',
            'sort_order' => 0,
        ]);
        $entry->delete();
        $report->delete();

        Livewire::actingAs($admin)
            ->test('admin.trash')
            ->call('restoreReport', $report->id)
            ->assertHasNoErrors();

        $this->assertNotSoftDeleted('daily_reports', ['id' => $report->id]);
        $this->assertNotSoftDeleted('work_log_entries', ['id' => $entry->id]);
    }

    public function test_admin_can_restore_a_deleted_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $report = DailyReport::query()->create([
            'user_id' => $user->id,
            'report_date' => today()->format('Y-m-d'),
            'first_submitted_at' => now(),
        ]);
        $entry = $report->entries()->create([
            'description' => 'Pekerjaan yang akan dipulihkan.',
            'sort_order' => 0,
        ]);
        $entry->delete();

        Livewire::actingAs($admin)
            ->test('admin.trash')
            ->call('restoreEntry', $entry->id)
            ->assertHasNoErrors();

        $this->assertNotSoftDeleted('work_log_entries', ['id' => $entry->id]);
    }

    public function test_admin_can_restore_a_deleted_attachment(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $report = DailyReport::query()->create([
            'user_id' => $user->id,
            'report_date' => today()->format('Y-m-d'),
            'first_submitted_at' => now(),
        ]);
        $entry = $report->entries()->create([
            'description' => 'Pekerjaan dengan lampiran.',
            'sort_order' => 0,
        ]);
        $attachment = $entry->attachments()->create([
            'user_id' => $user->id,
            'disk' => 'local',
            'path' => 'worklogs/'.$user->id.'/restore.jpg',
            'original_name' => 'restore.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
        ]);
        $attachment->delete();

        Livewire::actingAs($admin)
            ->test('admin.trash')
            ->call('restoreAttachment', $attachment->id)
            ->assertHasNoErrors();

        $this->assertNotSoftDeleted('attachments', ['id' => $attachment->id]);
    }
}
