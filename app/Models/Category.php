<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsActivityChanges;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'color', 'is_active', 'sort_order'])]
class Category extends Model
{
    use LogsActivityChanges;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function workLogEntries(): HasMany
    {
        return $this->hasMany(WorkLogEntry::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
