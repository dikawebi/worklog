<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigning_a_task_creates_a_notification_for_the_assignee(): void
    {
        $manager = User::factory()->manager()->create();
        $assignee = User::factory()->create();
        $project = $this->project($manager);

        Livewire::actingAs($manager)
            ->test('tasks.index')
            ->call('create')
            ->set('projectId', $project->id)
            ->set('assigneeId', $assignee->id)
            ->set('title', 'Menyiapkan backup server')
            ->set('priority', TaskPriority::Normal->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, $assignee->notifications()->count());
        $this->assertSame(0, $manager->notifications()->count());

        $notification = $assignee->notifications()->first();
        $this->assertSame('task_assigned', $notification->data['type']);
        $this->assertStringContainsString('Menyiapkan backup server', $notification->data['title']);
    }

    public function test_member_only_sees_their_own_notifications(): void
    {
        $manager = User::factory()->manager()->create();
        $member = User::factory()->create();
        $other = User::factory()->create();
        $task = $this->task($manager, $member, 'Task pribadi');

        $member->notify(new TaskAssigned($task, $manager));
        $other->notify(new TaskAssigned($task, $manager));

        Livewire::actingAs($member)
            ->test('notifications.index')
            ->assertSee('Task pribadi')
            ->assertViewHas('unreadCount', 1);

        $this->assertSame(1, $member->notifications()->count());
        $this->assertSame(1, $other->notifications()->count());
    }

    public function test_member_can_mark_all_notifications_as_read(): void
    {
        $manager = User::factory()->manager()->create();
        $member = User::factory()->create();
        $task = $this->task($manager, $member, 'Task notifikasi');

        $member->notify(new TaskAssigned($task, $manager));
        $member->notify(new TaskAssigned($task, $manager));

        Livewire::actingAs($member)
            ->test('notifications.index')
            ->call('markAllAsRead')
            ->assertSee('Semua notifikasi ditandai sudah dibaca.');

        $this->assertSame(0, $member->unreadNotifications()->count());
    }

    public function test_opening_a_notification_marks_it_as_read(): void
    {
        $manager = User::factory()->manager()->create();
        $member = User::factory()->create();
        $task = $this->task($manager, $member, 'Task yang dibuka');

        $member->notify(new TaskAssigned($task, $manager));
        $notification = $member->notifications()->first();

        Livewire::actingAs($member)
            ->test('notifications.index')
            ->call('open', $notification->id)
            ->assertRedirect(route('tasks.show', $task));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_unread_filter_only_shows_unread_notifications(): void
    {
        $manager = User::factory()->manager()->create();
        $member = User::factory()->create();
        $task = $this->task($manager, $member, 'Task campuran');

        $member->notify(new TaskAssigned($task, $manager));
        $member->notify(new TaskAssigned($task, $manager));
        $member->notifications()->first()->markAsRead();

        Livewire::actingAs($member)
            ->test('notifications.index')
            ->call('setFilter', 'unread')
            ->assertViewHas('notifications', fn ($notifications): bool => $notifications->total() === 1);
    }

    private function project(User $owner): Project
    {
        return Project::query()->create([
            'code' => 'PRJ-NOTIF',
            'name' => 'Project Notifikasi',
            'status' => ProjectStatus::Active,
            'owner_id' => $owner->id,
        ]);
    }

    private function task(User $manager, User $assignee, string $title): Task
    {
        $project = Project::query()->firstOrCreate(
            ['code' => 'PRJ-NOTIF'],
            [
                'name' => 'Project Notifikasi',
                'status' => ProjectStatus::Active,
                'owner_id' => $manager->id,
            ],
        );

        return Task::query()->create([
            'project_id' => $project->id,
            'assignee_id' => $assignee->id,
            'created_by_id' => $manager->id,
            'title' => $title,
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Normal,
        ]);
    }
}
