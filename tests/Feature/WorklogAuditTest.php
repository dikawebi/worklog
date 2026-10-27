<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class WorklogAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_changes_are_recorded_in_the_audit_log(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager);

        $project = Project::query()->create([
            'code' => 'PRJ-AUDIT',
            'name' => 'Nama Awal',
            'status' => ProjectStatus::Active,
            'owner_id' => $manager->id,
        ]);

        $project->update(['name' => 'Nama Baru']);

        $activity = Activity::query()
            ->where('subject_type', Project::class)
            ->where('subject_id', $project->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('updated', $activity->event);
        $this->assertSame('Nama Awal', $activity->properties['old']['name']);
        $this->assertSame('Nama Baru', $activity->properties['attributes']['name']);
        $this->assertSame($manager->id, $activity->causer_id);
    }

    public function test_removing_the_last_entry_soft_deletes_the_daily_report(): void
    {
        $user = User::factory()->create();
        $report = DailyReport::query()->create([
            'user_id' => $user->id,
            'report_date' => today()->format('Y-m-d'),
            'first_submitted_at' => now(),
        ]);
        $entry = $report->entries()->create([
            'description' => 'Pekerjaan terakhir hari ini.',
            'sort_order' => 0,
        ]);

        Livewire::actingAs($user)
            ->test('worklog.today', ['date' => today()->format('Y-m-d')])
            ->call('removeEntry', 0)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('daily_reports', ['id' => $report->id]);
        $this->assertSoftDeleted('work_log_entries', ['id' => $entry->id]);
    }

    public function test_removing_an_entry_is_recorded_in_the_audit_log(): void
    {
        $user = User::factory()->create();
        $report = DailyReport::query()->create([
            'user_id' => $user->id,
            'report_date' => today()->format('Y-m-d'),
            'first_submitted_at' => now(),
        ]);
        $entry = $report->entries()->create([
            'description' => 'Pekerjaan yang akan dihapus.',
            'sort_order' => 0,
        ]);

        $this->actingAs($user);
        $entry->delete();

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => $entry::class,
            'subject_id' => $entry->id,
            'event' => 'deleted',
            'causer_id' => $user->id,
        ]);
    }

    public function test_category_and_setting_changes_are_audited(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        $category = Category::query()->create([
            'name' => 'Audit',
            'color' => '#123456',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $category->update(['is_active' => false]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Category::class,
            'subject_id' => $category->id,
            'event' => 'updated',
            'causer_id' => $admin->id,
        ]);
    }
}
