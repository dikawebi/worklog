<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['app_name', 'timezone', 'workdays', 'reminder_time', 'mail_from_address', 'mail_from_name'])]
class Setting extends Model
{
    protected function casts(): array
    {
        return [
            'workdays' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'app_name' => 'IT Worklog',
            'timezone' => 'Asia/Jakarta',
            'workdays' => [1, 2, 3, 4, 5, 6],
            'reminder_time' => '17:00:00',
        ]);
    }
}
