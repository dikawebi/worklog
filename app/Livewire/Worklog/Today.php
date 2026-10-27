<?php

declare(strict_types=1);

namespace App\Livewire\Worklog;

use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\Task;
use App\Models\WorkLogEntry;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Today extends Component
{
    use WithFileUploads;

    public string $date;

    public array $entries = [];

    public array $attachments = [];

    public array $existingAttachments = [];

    public ?string $notes = null;

    public ?string $successMessage = null;

    public function mount(string $date): void
    {
        $this->date = $date;
        $this->loadReport();
    }

    public function updatedDate(): void
    {
        $this->successMessage = null;

        if ($this->validDate() === null) {
            return;
        }

        $this->resetValidation();
        $this->loadReport();
    }

    public function addEntry(): void
    {
        $this->successMessage = null;
        $this->entries[] = $this->emptyEntry();
        $this->attachments[] = [];
    }

    public function removeEntry(int $index): void
    {
        $this->successMessage = null;

        if (count($this->entries) === 1) {
            $this->entries[0] = $this->emptyEntry();
            $this->attachments[0] = [];
            $this->existingAttachments[0] = [];

            return;
        }

        unset($this->entries[$index]);
        $this->entries = array_values($this->entries);

        unset($this->attachments[$index], $this->existingAttachments[$index]);
        $this->attachments = array_values($this->attachments);
        $this->existingAttachments = array_values($this->existingAttachments);
    }

    public function save(): void
    {
        $this->successMessage = null;

        $reportDate = $this->validDate();

        if ($reportDate === null) {
            $this->addError('date', 'Tanggal worklog tidak valid.');

            return;
        }

        if ($reportDate->isFuture()) {
            $this->addError('date', 'Tanggal masa depan tidak dapat dipilih.');

            return;
        }

        $this->validate([
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')->whereNull('deleted_at')],
            'entries.*.task_id' => ['nullable', 'integer', Rule::exists('tasks', 'id')->whereNull('deleted_at')],
            'entries.*.category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'entries.*.description' => ['required', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array'],
        ], [
            'entries.*.description.required' => 'Deskripsi pekerjaan wajib diisi.',
        ]);

        foreach ($this->attachments as $index => $uploads) {
            $this->validate([
                "attachments.{$index}" => ['array', 'max:3'],
                "attachments.{$index}.*" => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            ], [
                "attachments.{$index}.max" => 'Maksimal 3 lampiran per pekerjaan.',
                "attachments.{$index}.*.mimes" => 'Lampiran harus berupa JPG, PNG, WEBP, atau PDF.',
                "attachments.{$index}.*.max" => 'Ukuran setiap lampiran maksimal 5 MB.',
            ]);
        }

        if (! $this->validateEntryRelationships()) {
            return;
        }

        DB::transaction(function () use ($reportDate): void {
            $report = DailyReport::query()->withTrashed()->firstOrNew([
                'user_id' => auth()->id(),
                'report_date' => $reportDate->toDateString(),
            ]);

            if ($report->trashed()) {
                $report->restore();
            }

            $isFirstSubmission = $report->first_submitted_at === null;
            $report->fill([
                'notes' => $this->notes ?: null,
                'first_submitted_at' => $report->first_submitted_at ?? now(),
                'last_edited_at' => $isFirstSubmission ? null : now(),
            ])->save();

            $keptEntryIds = [];

            foreach ($this->entries as $sortOrder => $entry) {
                $workLogEntry = $entry['id']
                    ? $report->entries()->whereKey($entry['id'])->firstOrFail()
                    : $report->entries()->make();

                $workLogEntry->fill([
                    'project_id' => $entry['project_id'] ?: null,
                    'task_id' => $entry['task_id'] ?: null,
                    'category_id' => $entry['category_id'] ?: null,
                    'description' => trim($entry['description']),
                    'sort_order' => $sortOrder,
                ])->save();

                $keptEntryIds[] = $workLogEntry->id;
                $this->storeAttachments($workLogEntry, $sortOrder);
            }

            $report->entries()->whereNotIn('id', $keptEntryIds)->delete();
        });

        $this->successMessage = 'Worklog berhasil disimpan.';
        $this->loadReport();
    }

    public function render()
    {
        return view('livewire.worklog.today', [
            'projects' => Project::query()
                ->whereNotIn('status', [ProjectStatus::Completed->value, ProjectStatus::Cancelled->value])
                ->orderBy('code')
                ->get(['id', 'code', 'name']),
            'tasks' => Task::query()
                ->with('project:id,code')
                ->orderBy('title')
                ->get(['id', 'project_id', 'title']),
            'categories' => Category::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'color']),
        ]);
    }

    private function loadReport(): void
    {
        $report = DailyReport::query()
            ->where('user_id', auth()->id())
            ->whereDate('report_date', $this->date)
            ->with(['entries' => fn ($query) => $query->with('attachments')->orderBy('sort_order')])
            ->first();

        $this->notes = $report?->notes;
        $this->attachments = [];
        $this->existingAttachments = [];
        $this->entries = $report?->entries->map(function ($entry, $index): array {
            $this->existingAttachments[$index] = $entry->attachments->map(fn ($attachment): array => [
                'id' => $attachment->id,
                'name' => $attachment->original_name,
                'mime' => $attachment->mime_type,
                'size' => $attachment->size,
                'url' => route('attachments.download', $attachment),
            ])->all();

            return [
                'id' => $entry->id,
                'project_id' => $entry->project_id,
                'task_id' => $entry->task_id,
                'category_id' => $entry->category_id,
                'description' => $entry->description,
            ];
        })->all() ?: [$this->emptyEntry()];
    }

    private function emptyEntry(): array
    {
        return [
            'id' => null,
            'project_id' => null,
            'task_id' => null,
            'category_id' => Category::query()->where('name', 'Umum')->value('id'),
            'description' => '',
        ];
    }

    private function storeAttachments(WorkLogEntry $entry, int $index): void
    {
        foreach ($this->attachments[$index] ?? [] as $file) {
            $path = $file->store('worklogs/'.auth()->id(), 'local');

            $entry->attachments()->create([
                'user_id' => auth()->id(),
                'disk' => 'local',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    private function validDate(): ?CarbonImmutable
    {
        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $this->date);
        } catch (\Throwable) {
            return null;
        }

        return $date && $date->format('Y-m-d') === $this->date ? $date : null;
    }

    private function validateEntryRelationships(): bool
    {
        foreach ($this->entries as $index => $entry) {
            if (! $entry['task_id'] || ! $entry['project_id']) {
                continue;
            }

            $taskBelongsToProject = Task::query()
                ->whereKey($entry['task_id'])
                ->where('project_id', $entry['project_id'])
                ->exists();

            if (! $taskBelongsToProject) {
                $this->addError("entries.{$index}.task_id", 'Task tidak termasuk dalam project yang dipilih.');
            }
        }

        return $this->getErrorBag()->isEmpty();
    }
}
