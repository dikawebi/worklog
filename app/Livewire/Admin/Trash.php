<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Attachment;
use App\Models\DailyReport;
use App\Models\WorkLogEntry;
use Livewire\Component;

class Trash extends Component
{
    public string $tab = 'reports';

    public string $search = '';

    public ?string $successMessage = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['reports', 'entries', 'attachments'], true)) {
            return;
        }

        $this->tab = $tab;
        $this->successMessage = null;
    }

    public function restoreReport(int $reportId): void
    {
        $report = DailyReport::query()->onlyTrashed()->findOrFail($reportId);

        $report->restore();
        $report->entries()->withTrashed()->get()->each->restore();

        $this->successMessage = 'Laporan dan pekerjaannya berhasil dipulihkan.';
    }

    public function restoreEntry(int $entryId): void
    {
        $entry = WorkLogEntry::query()->onlyTrashed()->with(['dailyReport' => fn ($query) => $query->withTrashed()])->findOrFail($entryId);

        if ($entry->dailyReport?->trashed()) {
            $entry->dailyReport->restore();
        }

        $entry->restore();
        $this->successMessage = 'Pekerjaan berhasil dipulihkan.';
    }

    public function restoreAttachment(int $attachmentId): void
    {
        $attachment = Attachment::query()->onlyTrashed()->findOrFail($attachmentId);
        $attachment->restore();

        $this->successMessage = 'Lampiran berhasil dipulihkan.';
    }

    public function render()
    {
        $reports = collect();
        $entries = collect();
        $attachments = collect();

        if ($this->tab === 'reports') {
            $reports = DailyReport::query()
                ->onlyTrashed()
                ->with('user:id,name')
                ->withCount(['entries' => fn ($query) => $query->withTrashed()])
                ->when($this->search, fn ($query) => $query->whereHas('user', fn ($user) => $user->where('name', 'like', '%'.$this->search.'%')))
                ->orderByDesc('deleted_at')
                ->limit(50)
                ->get();
        }

        if ($this->tab === 'entries') {
            $entries = WorkLogEntry::query()
                ->onlyTrashed()
                ->with([
                    'dailyReport' => fn ($query) => $query->withTrashed()->select('id', 'user_id', 'report_date', 'deleted_at'),
                    'dailyReport.user:id,name',
                ])
                ->when($this->search, fn ($query) => $query->where('description', 'like', '%'.$this->search.'%'))
                ->orderByDesc('deleted_at')
                ->limit(50)
                ->get();
        }

        if ($this->tab === 'attachments') {
            $attachments = Attachment::query()
                ->onlyTrashed()
                ->with('user:id,name')
                ->when($this->search, fn ($query) => $query->where('original_name', 'like', '%'.$this->search.'%'))
                ->orderByDesc('deleted_at')
                ->limit(50)
                ->get();
        }

        return view('livewire.admin.trash', [
            'reports' => $reports,
            'entries' => $entries,
            'attachments' => $attachments,
            'counts' => [
                'reports' => DailyReport::query()->onlyTrashed()->count(),
                'entries' => WorkLogEntry::query()->onlyTrashed()->count(),
                'attachments' => Attachment::query()->onlyTrashed()->count(),
            ],
        ]);
    }
}
