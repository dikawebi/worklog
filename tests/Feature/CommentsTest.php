<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\MentionedInComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_comment_on_a_task_and_mention_another_member(): void
    {
        $manager = User::factory()->manager()->create(['name' => 'Manager Diskusi']);
        $assignee = User::factory()->create(['name' => 'Pemilik Task']);
        $mentioned = User::factory()->create([
            'name' => 'Andi',
            'email' => 'andi@borneoprima.com',
        ]);
        $task = $this->task($manager, $assignee);

        Livewire::actingAs($manager)
            ->test('comments', [
                'commentableType' => Task::class,
                'commentableId' => $task->id,
            ])
            ->set('body', '@andi mohon cek hasil konfigurasi server.')
            ->call('addComment')
            ->assertHasNoErrors()
            ->assertSee('Komentar berhasil dikirim.');

        $this->assertDatabaseHas('comments', [
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
            'user_id' => $manager->id,
            'body' => '@andi mohon cek hasil konfigurasi server.',
        ]);

        $this->assertSame(1, $mentioned->notifications()->count());
        $this->assertSame(MentionedInComment::class, $mentioned->notifications()->first()->type);
        $this->assertSame(0, $assignee->notifications()->count());
    }

    public function test_member_cannot_comment_on_a_task_they_cannot_view(): void
    {
        $manager = User::factory()->manager()->create();
        $assignee = User::factory()->create();
        $outsider = User::factory()->create();
        $task = $this->task($manager, $assignee);

        Livewire::actingAs($outsider)
            ->test('comments', [
                'commentableType' => Task::class,
                'commentableId' => $task->id,
            ])
            ->assertForbidden();
    }

    public function test_user_can_delete_their_own_comment(): void
    {
        $manager = User::factory()->manager()->create();
        $assignee = User::factory()->create();
        $task = $this->task($manager, $assignee);
        $comment = Comment::query()->create([
            'user_id' => $manager->id,
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
            'body' => 'Komentar yang akan dihapus.',
        ]);

        Livewire::actingAs($manager)
            ->test('comments', [
                'commentableType' => Task::class,
                'commentableId' => $task->id,
            ])
            ->call('deleteComment', $comment->id)
            ->assertSee('Komentar berhasil dihapus.');

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    private function task(User $manager, User $assignee): Task
    {
        $project = Project::query()->create([
            'code' => 'PRJ-COMMENT',
            'name' => 'Project Diskusi',
            'status' => ProjectStatus::Active,
            'owner_id' => $manager->id,
        ]);

        return Task::query()->create([
            'project_id' => $project->id,
            'assignee_id' => $assignee->id,
            'created_by_id' => $manager->id,
            'title' => 'Task dengan diskusi',
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Normal,
        ]);
    }
}
