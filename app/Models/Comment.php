<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsActivityChanges;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['user_id', 'commentable_type', 'commentable_id', 'body'])]
class Comment extends Model
{
    use LogsActivityChanges;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
