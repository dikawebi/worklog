<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class MentionedInComment extends Notification
{
    use Queueable;

    public function __construct(
        public Comment $comment,
        public User $author,
        public string $url,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mention',
            'title' => $this->author->name.' menyebut Anda dalam komentar',
            'message' => Str::limit($this->comment->body, 160),
            'url' => $this->url,
            'comment_id' => $this->comment->id,
        ];
    }
}
