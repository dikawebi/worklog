<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkLogEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_only_sees_their_own_worklog_in_reports(): void
    {
        $first = User::factory()->create(['name' => 'Anggota Pertama']);
        $second = User::factory()->create(['name' => 'Anggota Kedua']);
        $this->report($first, 'Pekerjaan anggota pertama.');
        $this->report($second, 'Pekerjaan anggota kedua.');

        Livewire::actingAs($first)
            ->test('reports.index')
            ->assertSee('Pekerjaan anggota pertama.')
            ->assertDontSee('Pekerjaan anggota kedua.');
    }

    public function test_manager_sees_all_members_in_reports(): void
    {
        $manager = User::factory()->manager()->create();
        $member = User::factory()->create(['name' => 'Anggota Tim']);
        $this->report($member, 'Pekerjaan anggota tim.');

        Livewire::actingAs($manager)
            ->test('reports.index')
            ->assertSee('Pekerjaan anggota tim.')
            ->assertSee('Anggota Tim');
    }

    public function test_report_summary_counts_entries_days_and_members(): void
    {
        $user = User::factory()->create();
        $this->report($user, 'Pekerjaan pertama.');
        $this->report($user, 'Pekerjaan kedua.');

        Livewire::actingAs($user)
            ->test('reports.index')
            ->assertViewHas('summary', fn (array $summary): bool => $summary['entries'] === 2
                && $summary['days'] === 1
                && $summary['members'] === 1);
    }

    public function test_report_can_be_filtered_by_project(): void
    {
        $user = User::factory()->create();
        $firstProject = Project::query()->create([
            'code' => 'PRJ-REPORT-A',
            'name' => 'Project Laporan A',
            'status' => 'active',
            'owner_id' => $user->id,
        ]);
        $secondProject = Project::query()->create([
            'code' => 'PRJ-REPORT-B',
            'name' => 'Project Laporan B',
            'status' => 'active',
            'owner_id' => $user->id,
        ]);
        $this->report($user, 'Pekerjaan project A', $firstProject);
        $this->report($user, 'Pekerjaan project B', $secondProject);

        Livewire::actingAs($user)
            ->test('reports.index')
            ->set('projectFilter', (string) $firstProject->id)
            ->assertSee('Pekerjaan project A')
            ->assertDontSee('Pekerjaan project B');
    }

    public function test_report_can_be_exported_as_csv(): void
    {
        $user = User::factory()->create(['name' => 'Andika']);
        $this->report($user, 'Pekerjaan export CSV.');

        $response = $this->actingAs($user)->get(route('reports.export', [
            'from' => today()->startOfMonth()->toDateString(),
            'to' => today()->toDateString(),
        ]));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Tanggal,Anggota,Jabatan,Project,Kategori,Pekerjaan,Lampiran', $csv);
        $this->assertStringContainsString('Pekerjaan export CSV.', $csv);
        $this->assertStringContainsString('Andika', $csv);
    }

    public function test_report_print_view_can_be_opened(): void
    {
        $user = User::factory()->create();
        $this->report($user, 'Pekerjaan print view.');

        $this->actingAs($user)
            ->get(route('reports.print', [
                'from' => today()->startOfMonth()->toDateString(),
                'to' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Laporan Aktivitas Worklog')
            ->assertSee('Pekerjaan print view.');
    }

    private function report(User $user, string $description, ?Project $project = null): WorkLogEntry
    {
        $report = DailyReport::query()
            ->where('user_id', $user->id)
            ->whereDate('report_date', today())
            ->first();

        if (! $report) {
            $report = DailyReport::query()->create([
                'user_id' => $user->id,
                'report_date' => today()->format('Y-m-d'),
                'first_submitted_at' => now(),
            ]);
        }

        return $report->entries()->create([
            'project_id' => $project?->id,
            'category_id' => Category::query()->firstOrCreate(
                ['name' => 'Umum'],
                ['color' => '#64748B', 'is_active' => true, 'sort_order' => 0],
            )->id,
            'description' => $description,
            'sort_order' => 0,
        ]);
    }
}
