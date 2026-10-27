<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_see_team_activity(): void
    {
        $author = User::factory()->create(['name' => 'Andika Kuswidyarto']);
        $viewer = User::factory()->create(['name' => 'Budi Santoso']);
        $report = DailyReport::query()->create([
            'user_id' => $author->id,
            'report_date' => today()->format('Y-m-d'),
            'first_submitted_at' => now(),
        ]);
        $report->entries()->create([
            'description' => 'Perbaikan jaringan lantai dua.',
            'sort_order' => 0,
        ]);

        Livewire::actingAs($viewer)
            ->test('worklog.feed')
            ->assertSee('Andika Kuswidyarto')
            ->assertSee('Perbaikan jaringan lantai dua.')
            ->assertSee('1 pekerjaan');
    }

    public function test_user_can_open_worklog_detail_from_activity_feed(): void
    {
        $author = User::factory()->create(['name' => 'Citra Rahma']);
        $report = DailyReport::query()->create([
            'user_id' => $author->id,
            'report_date' => today()->format('Y-m-d'),
            'first_submitted_at' => now(),
        ]);
        $report->entries()->create([
            'description' => 'Review konfigurasi firewall.',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('worklogs.show', $report));

        $response->assertOk()
            ->assertSee('Worklog Citra Rahma')
            ->assertSee('Review konfigurasi firewall.');
    }
}
