<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\WorkLogEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ScheduledWorklogReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{from: string, to: string, user: ?int, project: ?int, category: ?int}  $filters
     * @param  array{entries: int, days: int, members: int}  $summary
     * @param  Collection<int, object>  $perUser
     * @param  Collection<int, object>  $perProject
     * @param  Collection<int, WorkLogEntry>  $entries
     */
    public function __construct(
        public array $filters,
        public array $summary,
        public Collection $perUser,
        public Collection $perProject,
        public Collection $entries,
        public string $frequency,
        public string $csv,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laporan worklog '.$this->periodLabel().' — IT Worklog',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.scheduled-report',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn (): string => $this->csv, 'worklog-'.$this->filters['from'].'-'.$this->filters['to'].'.csv')
                ->withMime('text/csv'),
        ];
    }

    public function periodLabel(): string
    {
        return match ($this->frequency) {
            'daily' => 'harian',
            'monthly' => 'bulanan',
            default => 'mingguan',
        };
    }
}
