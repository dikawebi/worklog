<?php

declare(strict_types=1);

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Blocked = 'blocked';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'Belum dikerjakan',
            self::InProgress => 'Dikerjakan',
            self::Blocked => 'Terhambat',
            self::Done => 'Selesai',
        };
    }
}
