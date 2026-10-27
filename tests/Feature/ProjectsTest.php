<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
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
}
