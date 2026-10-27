<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_view_the_audit_log(): void
    {
        $member = User::factory()->create();

        Livewire::actingAs($member)
            ->test('admin.audit-log')
            ->assertForbidden();
    }

    public function test_admin_can_view_audit_entries(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Project::query()->create([
            'code' => 'PRJ-AUDIT-VIEW',
            'name' => 'Project Audit Tampil',
            'status' => ProjectStatus::Active,
            'owner_id' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test('admin.audit-log')
            ->assertSee('Project Audit Tampil')
            ->assertSee('Created')
            ->assertSee('Project');
    }

    public function test_audit_log_can_be_filtered_by_event(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        $project = Project::query()->create([
            'code' => 'PRJ-AUDIT-FILTER',
            'name' => 'Project Filter',
            'status' => ProjectStatus::Active,
            'owner_id' => $admin->id,
        ]);
        $project->update(['name' => 'Project Filter Baru']);

        Livewire::actingAs($admin)
            ->test('admin.audit-log')
            ->set('eventFilter', 'created')
            ->assertSee('Project Filter')
            ->assertDontSee('Project Filter Baru');
    }
}
