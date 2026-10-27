<?php

namespace Tests\Feature;

use App\Enums\MilestoneStatus;
use App\Enums\ProjectStatus;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_a_project(): void
    {
        $manager = User::factory()->manager()->create();

        Livewire::actingAs($manager)
            ->test('projects.index')
            ->call('create')
            ->set('code', 'PRJ-001')
            ->set('name', 'Migrasi Server')
            ->set('status', ProjectStatus::Planning->value)
            ->set('ownerId', $manager->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'code' => 'PRJ-001',
            'name' => 'Migrasi Server',
            'owner_id' => $manager->id,
        ]);
    }

    public function test_member_cannot_create_a_project(): void
    {
        $member = User::factory()->create();

        Livewire::actingAs($member)
            ->test('projects.index')
            ->call('create')
            ->assertForbidden();
    }

    public function test_project_detail_can_be_viewed_by_a_member(): void
    {
        $owner = User::factory()->manager()->create();
        $member = User::factory()->create();
        $project = Project::query()->create([
            'code' => 'PRJ-002',
            'name' => 'Support Internal',
            'status' => ProjectStatus::Active,
            'owner_id' => $owner->id,
        ]);
        $project->members()->attach($member->id);

        $this->actingAs($member)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Support Internal');
    }

    public function test_milestone_is_overdue_when_past_due_date_and_not_completed(): void
    {
        $owner = User::factory()->manager()->create();
        $project = Project::query()->create([
            'code' => 'PRJ-003',
            'name' => 'Jaringan Kantor',
            'status' => ProjectStatus::Active,
            'owner_id' => $owner->id,
        ]);
        $overdue = Milestone::query()->create([
            'project_id' => $project->id,
            'title' => 'Milestone terlambat',
            'status' => MilestoneStatus::Pending,
            'due_date' => today()->subDay(),
            'sort_order' => 0,
        ]);
        Milestone::query()->create([
            'project_id' => $project->id,
            'title' => 'Milestone selesai',
            'status' => MilestoneStatus::Completed,
            'due_date' => today()->subDay(),
            'sort_order' => 1,
        ]);

        $this->assertTrue(Milestone::query()->overdue()->whereKey($overdue->id)->exists());
        $this->assertSame(1, Milestone::query()->overdue()->count());
    }
}
