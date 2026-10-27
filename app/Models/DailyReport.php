<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsActivityChanges;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'report_date', 'notes', 'first_submitted_at', 'last_edited_at'])]
class DailyReport extends Model
{
    use LogsActivityChanges, SoftDeletes;

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'first_submitted_at' => 'datetime',
            'last_edited_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(WorkLogEntry::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
