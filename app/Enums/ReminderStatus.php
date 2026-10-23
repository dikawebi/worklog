<?php

declare(strict_types=1);

namespace App\Enums;

enum ReminderStatus: string
{
    case Sent = 'sent';
    case Failed = 'failed';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::Sent => 'Terkirim',
            self::Failed => 'Gagal',
            self::Skipped => 'Dilewati',
        };
    }
}
