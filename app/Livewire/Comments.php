<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Comment;
use App\Models\DailyReport;
use App\Models\Task;
use App\Models\User;
use App\Notifications\MentionedInComment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;

class Comments extends Component
{
    public string $commentableType;

    public int $commentableId;

    public string $body = '';

    public ?string $successMessage = null;

    public function mount(string $commentableType, int $commentableId): void
    {
        abort_unless(in_array($commentableType, [Task::class, DailyReport::class], true), 404);

        $this->commentableType = $commentableType;
        $this->commentableId = $commentableId;
        $this->commentable();
    }

    public function addComment(): void
    {
        $this->validate([
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'body.required' => 'Komentar wajib diisi.',
        ]);

        $commentable = $this->commentable();
        $comment = $commentable->comments()->create([
            'user_id' => auth()->id(),
            'body' => trim($this->body),
        ]);

        $this->notifyMentions($comment, $commentable);
        $this->body = '';
        $this->successMessage = 'Komentar berhasil dikirim.';
    }

    public function deleteComment(int $commentId): void
    {
        $comment = Comment::query()
            ->whereKey($commentId)
            ->where('commentable_type', $this->commentableType)
            ->where('commentable_id', $this->commentableId)
            ->firstOrFail();

        abort_unless($comment->user_id === auth()->id() || auth()->user()->isAdmin(), 403);

        $comment->delete();
        $this->successMessage = 'Komentar berhasil dihapus.';
    }

    public function render()
    {
        $commentable = $this->commentable();

        return view('livewire.comments', [
            'comments' => $commentable->comments()->with('user:id,name')->oldest()->get(),
            'mentionUsers' => User::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
                ->map(fn (User $user): array => [
                    'handle' => Str::before($user->email, '@'),
                    'name' => $user->name,
                ]),
        ]);
    }

    private function commentable(): Task|DailyReport
    {
        /** @var Task|DailyReport $model */
        $model = $this->commentableType::query()->findOrFail($this->commentableId);

        if ($model instanceof Task) {
            Gate::authorize('view', $model);
        }

        return $model;
    }

    private function notifyMentions(Comment $comment, Task|DailyReport $commentable): void
    {
        preg_match_all('/(?<![\w.@])@([A-Za-z0-9._-]+)/', $comment->body, $matches);

        $handles = collect($matches[1] ?? [])->map(fn (string $handle): string => Str::lower($handle))->unique();

        if ($handles->isEmpty()) {
            return;
        }

        $users = User::query()
            ->where('is_active', true)
            ->get()
            ->filter(function (User $user) use ($handles): bool {
                $email = Str::lower($user->email);
                $local = Str::before($email, '@');
                $name = Str::lower($user->name);

                $nameWords = preg_split('/\s+/', $name) ?: [];

                foreach ($handles as $handle) {
                    if ($local === $handle) {
                        return true;
                    }

                    $dotCount = substr_count($handle, '.') + 1;
                    $prefix = implode(' ', array_slice($nameWords, 0, $dotCount));

                    if (strtolower($prefix) === $handle) {
                        return true;
                    }
                }

                return false;
            })
            ->values();

        $url = $commentable instanceof Task
            ? route('tasks.show', $commentable)
            : route('worklogs.show', $commentable);

        foreach ($users as $user) {
            if ($user->id !== auth()->id()) {
                $user->notify(new MentionedInComment($comment, auth()->user(), $url));
            }
        }
    }
}
