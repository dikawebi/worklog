<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_the_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk()
            ->assertSee('Selamat datang kembali')
            ->assertSee('PT BORNEO PRIMA')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_member_does_not_see_admin_menu(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk()
            ->assertDontSee('Audit Log')
            ->assertDontSee('Belum isi hari ini');
    }

    public function test_dashboard_shows_personal_week_metrics(): void
    {
        $user = User::factory()->create();
        $report = DailyReport::query()->create([
            'user_id' => $user->id,
            'report_date' => today()->format('Y-m-d'),
            'first_submitted_at' => now(),
        ]);
        $report->entries()->create([
            'description' => 'Pekerjaan dashboard.',
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertViewHas('myWeekEntries', 1)
            ->assertViewHas('myWeekDays', 1)
            ->assertSee('Pekerjaan saya minggu ini');
    }

    public function test_manager_dashboard_shows_members_who_have_not_submitted(): void
    {
        $manager = User::factory()->manager()->create();
        $member = User::factory()->create(['name' => 'Anggota Belum Isi']);

        $this->actingAs($manager)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Belum isi hari ini')
            ->assertViewHas('pendingTodayUsers', fn ($users): bool => $users->contains('id', $member->id));
    }
}
