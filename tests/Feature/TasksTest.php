<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_page_can_be_viewed_by_a_member(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('Tugas');
    }

    public function test_manager_can_create_a_task(): void
    {
        $manager = User::factory()->manager()->create();
        $assignee = User::factory()->create();
        $project = $this->project($manager, 'PRJ-001');

        Livewire::actingAs($manager)
            ->test('tasks.index')
            ->call('create')
            ->set('projectId', $project->id)
            ->set('assigneeId', $assignee->id)
            ->set('title', 'Menyiapkan server staging')
            ->set('description', 'Instalasi dan konfigurasi dasar.')
            ->set('priority', TaskPriority::High->value)
            ->set('dueDate', today()->addDays(3)->format('Y-m-d'))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'assignee_id' => $assignee->id,
            'title' => 'Menyiapkan server staging',
            'status' => TaskStatus::Todo->value,
            'priority' => TaskPriority::High->value,
            'created_by_id' => $manager->id,
        ]);
    }

    public function test_member_cannot_create_a_task(): void
    {
        $member = User::factory()->create();

        Livewire::actingAs($member)
            ->test('tasks.index')
            ->call('create')
            ->assertForbidden();
    }

    public function test_member_only_sees_tasks_assigned_to_them(): void
    {
        $manager = User::factory()->manager()->create();
        $first = User::factory()->create();
        $second = User::factory()->create();
        $project = $this->project($manager, 'PRJ-002');

        $this->task($project, $first, 'Task untuk anggota pertama');
        $this->task($project, $second, 'Task untuk anggota kedua');

        Livewire::actingAs($first)
            ->test('tasks.index')
            ->assertSee('Task untuk anggota pertama')
            ->assertDontSee('Task untuk anggota kedua');
    }

    public function test_assignee_can_move_a_task_from_todo_to_done(): void
    {
        $manager = User::factory()->manager()->create();
        $assignee = User::factory()->create();
        $project = $this->project($manager, 'PRJ-003');
        $task = $this->task($project, $assignee, 'Deploy staging');

        Livewire::actingAs($assignee)
            ->test('tasks.index')
            ->call('changeStatus', $task->id, TaskStatus::InProgress->value)
            ->assertHasNoErrors();

        $task->refresh();
        $this->assertSame(TaskStatus::InProgress, $task->status);
        $this->assertNotNull($task->started_at);

        Livewire::actingAs($assignee)
            ->test('tasks.index')
            ->call('changeStatus', $task->id, TaskStatus::Done->value)
            ->assertHasNoErrors();

        $task->refresh();
        $this->assertSame(TaskStatus::Done, $task->status);
        $this->assertNotNull($task->completed_at);
    }

    public function test_non_assignee_cannot_change_task_status(): void
    {
        $manager = User::factory()->manager()->create();
        $assignee = User::factory()->create();
        $other = User::factory()->create();
        $project = $this->project($manager, 'PRJ-004');
        $task = $this->task($project, $assignee, 'Review firewall');

        Livewire::actingAs($other)
            ->test('tasks.index')
            ->call('changeStatus', $task->id, TaskStatus::InProgress->value)
            ->assertForbidden();
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $manager = User::factory()->manager()->create();
        $assignee = User::factory()->create();
        $project = $this->project($manager, 'PRJ-005');
        $task = $this->task($project, $assignee, 'Belum dimulai');

        Livewire::actingAs($assignee)
            ->test('tasks.index')
            ->call('changeStatus', $task->id, TaskStatus::Done->value)
            ->assertHasErrors('status');

        $this->assertSame(TaskStatus::Todo, $task->refresh()->status);
    }

    public function test_milestone_must_belong_to_the_selected_project(): void
    {
        $manager = User::factory()->manager()->create();
        $project = $this->project($manager, 'PRJ-006');
        $otherProject = $this->project($manager, 'PRJ-007');
        $milestone = Milestone::query()->create([
            'project_id' => $otherProject->id,
            'title' => 'Milestone project lain',
            'status' => 'pending',
            'sort_order' => 0,
        ]);

        Livewire::actingAs($manager)
            ->test('tasks.index')
            ->call('create')
            ->set('projectId', $project->id)
            ->set('milestoneId', $milestone->id)
            ->set('title', 'Task dengan milestone salah')
            ->call('save')
            ->assertHasErrors('milestoneId');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_member_cannot_edit_a_task(): void
    {
        $manager = User::factory()->manager()->create();
        $assignee = User::factory()->create();
        $project = $this->project($manager, 'PRJ-008');
        $task = $this->task($project, $assignee, 'Task hanya untuk manager');

        Livewire::actingAs($assignee)
            ->test('tasks.index')
            ->call('edit', $task->id)
            ->assertForbidden();
    }

    private function project(User $owner, string $code): Project
    {
        return Project::query()->create([
            'code' => $code,
            'name' => 'Project '.$code,
            'status' => ProjectStatus::Active,
            'owner_id' => $owner->id,
        ]);
    }

    private function task(Project $project, User $assignee, string $title): Task
    {
        return Task::query()->create([
            'project_id' => $project->id,
            'assignee_id' => $assignee->id,
            'created_by_id' => $project->owner_id,
            'title' => $title,
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Normal,
        ]);
    }
}
