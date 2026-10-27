<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorklogTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_today_worklog(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('worklogs.index'));

        $response->assertOk()
            ->assertSee('Catat pekerjaan harian')
            ->assertSee('Simpan Worklog');
    }

    public function test_user_can_save_a_worklog_with_a_default_category(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Umum',
            'color' => '#64748B',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Livewire::actingAs($user)
            ->test('worklog.today', ['date' => today()->format('Y-m-d')])
            ->set('entries.0.description', 'Menyiapkan backup server staging.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Worklog berhasil disimpan.')
            ->assertSet('entries.0.id', null)
            ->assertSet('entries.0.description', '')
            ->assertSet('notes', null);

        $this->assertTrue(DailyReport::query()
            ->where('user_id', $user->id)
            ->whereDate('report_date', today())
            ->exists());
        $this->assertDatabaseHas('work_log_entries', [
            'category_id' => $category->id,
            'description' => 'Menyiapkan backup server staging.',
        ]);
    }

    public function test_worklog_requires_a_description(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('worklog.today', ['date' => today()->format('Y-m-d')])
            ->call('save')
            ->assertHasErrors(['entries.0.description']);

        $this->assertDatabaseCount('daily_reports', 0);
    }

    public function test_future_worklog_is_rejected(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('worklog.today', ['date' => today()->addDay()->format('Y-m-d')])
            ->set('entries.0.description', 'Pekerjaan masa depan')
            ->call('save')
            ->assertHasErrors(['date']);

        $this->assertDatabaseCount('daily_reports', 0);
    }

    public function test_worklog_task_options_are_filtered_by_selected_project(): void
    {
        $user = User::factory()->manager()->create();
        $firstProject = Project::query()->create([
            'code' => 'PRJ-WL-A',
            'name' => 'Project Worklog A',
            'status' => 'active',
            'owner_id' => $user->id,
        ]);
        $secondProject = Project::query()->create([
            'code' => 'PRJ-WL-B',
            'name' => 'Project Worklog B',
            'status' => 'active',
            'owner_id' => $user->id,
        ]);
        Task::query()->create([
            'project_id' => $firstProject->id,
            'created_by_id' => $user->id,
            'title' => 'Task di project A',
            'status' => 'todo',
            'priority' => 'normal',
        ]);
        Task::query()->create([
            'project_id' => $secondProject->id,
            'created_by_id' => $user->id,
            'title' => 'Task di project B',
            'status' => 'todo',
            'priority' => 'normal',
        ]);

        Livewire::actingAs($user)
            ->test('worklog.today', ['date' => today()->format('Y-m-d')])
            ->set('entries.0.project_id', (string) $firstProject->id)
            ->assertSee('Task di project A')
            ->assertDontSee('Task di project B');
    }

    public function test_worklog_can_be_opened_with_a_task_prefilled(): void
    {
        $manager = User::factory()->manager()->create();
        $project = Project::query()->create([
            'code' => 'PRJ-WL-PREFILL',
            'name' => 'Project Prefill',
            'status' => 'active',
            'owner_id' => $manager->id,
        ]);
        $task = Task::query()->create([
            'project_id' => $project->id,
            'created_by_id' => $manager->id,
            'title' => 'Task untuk worklog',
            'status' => 'todo',
            'priority' => 'normal',
        ]);

        Livewire::actingAs($manager)
            ->test('worklog.today', [
                'date' => today()->format('Y-m-d'),
                'taskId' => $task->id,
            ])
            ->assertSet('entries.0.task_id', $task->id)
            ->assertSet('entries.0.project_id', $project->id);
    }
}
