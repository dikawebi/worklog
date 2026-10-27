<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkLogEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_search_only_returns_their_own_worklogs(): void
    {
        $first = User::factory()->create(['name' => 'Pencari Pertama']);
        $second = User::factory()->create(['name' => 'Pencari Kedua']);
        $this->report($first, 'Pekerjaan rahasia pertama.');
        $this->report($second, 'Pekerjaan rahasia kedua.');

        $this->actingAs($first)
            ->get(route('search', ['q' => 'rahasia']))
            ->assertOk()
            ->assertSee('Pekerjaan rahasia pertama.')
            ->assertDontSee('Pekerjaan rahasia kedua.');
    }

    public function test_manager_search_returns_worklogs_from_all_members(): void
    {
        $manager = User::factory()->manager()->create();
        $member = User::factory()->create();
        $this->report($member, 'Pekerjaan pencarian tim.');

        $this->actingAs($manager)
            ->get(route('search', ['q' => 'pencarian tim']))
            ->assertOk()
            ->assertSee('Pekerjaan pencarian tim.');
    }

    public function test_search_finds_tasks_by_title(): void
    {
        $manager = User::factory()->manager()->create();
        $project = Project::query()->create([
            'code' => 'PRJ-SEARCH',
            'name' => 'Project Search',
            'status' => ProjectStatus::Active,
            'owner_id' => $manager->id,
        ]);
        Task::query()->create([
            'project_id' => $project->id,
            'created_by_id' => $manager->id,
            'title' => 'Audit keamanan firewall',
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Normal,
        ]);

        $this->actingAs($manager)
            ->get(route('search', ['q' => 'firewall']))
            ->assertOk()
            ->assertSee('Audit keamanan firewall')
            ->assertSee('Project Search');
    }

    public function test_search_requires_at_least_two_characters(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('search', ['q' => 'a']))
            ->assertOk()
            ->assertSee('Masukkan minimal 2 karakter.');
    }

    private function report(User $user, string $description): WorkLogEntry
    {
        $report = DailyReport::query()->create([
            'user_id' => $user->id,
            'report_date' => today()->format('Y-m-d'),
            'first_submitted_at' => now(),
        ]);

        return $report->entries()->create([
            'description' => $description,
            'sort_order' => 0,
        ]);
    }
}
