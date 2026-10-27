<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Setting;
use DateTimeZone;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Settings extends Component
{
    public string $appName = 'IT Worklog';

    public string $timezone = 'Asia/Jakarta';

    public array $workdays = [1, 2, 3, 4, 5, 6];

    public string $reminderTime = '17:00';

    public ?string $mailFromAddress = null;

    public ?string $mailFromName = null;

    public ?string $successMessage = null;

    public function mount(): void
    {
        Gate::authorize('viewAny', Setting::class);

        $settings = Setting::current();

        $this->appName = $settings->app_name;
        $this->timezone = $settings->timezone;
        $this->workdays = array_map('intval', $settings->workdays ?? [1, 2, 3, 4, 5, 6]);
        $this->reminderTime = substr((string) $settings->reminder_time, 0, 5);
        $this->mailFromAddress = $settings->mail_from_address;
        $this->mailFromName = $settings->mail_from_name;
    }

    public function save(): void
    {
        $settings = Setting::current();
        Gate::authorize('update', $settings);

        $this->validate([
            'appName' => ['required', 'string', 'max:100'],
            'timezone' => ['required', 'string', 'timezone'],
            'workdays' => ['required', 'array', 'min:1'],
            'workdays.*' => ['integer', 'between:1,7'],
            'reminderTime' => ['required', 'date_format:H:i'],
            'mailFromAddress' => ['nullable', 'email', 'max:255'],
            'mailFromName' => ['nullable', 'string', 'max:100'],
        ]);

        $workdays = array_values(array_unique(array_map('intval', $this->workdays)));
        sort($workdays);

        $settings->update([
            'app_name' => $this->appName,
            'timezone' => $this->timezone,
            'workdays' => $workdays,
            'reminder_time' => $this->reminderTime.':00',
            'mail_from_address' => $this->mailFromAddress ?: null,
            'mail_from_name' => $this->mailFromName ?: null,
        ]);

        config([
            'app.name' => $settings->app_name,
            'app.timezone' => $settings->timezone,
            'mail.from.address' => $settings->mail_from_address ?: config('mail.from.address'),
            'mail.from.name' => $settings->mail_from_name ?: config('mail.from.name'),
        ]);

        date_default_timezone_set($settings->timezone);

        $this->successMessage = 'Pengaturan berhasil disimpan.';
    }

    public function render()
    {
        $timezones = collect(DateTimeZone::listIdentifiers())
            ->filter(fn (string $timezone): bool => str_starts_with($timezone, 'Asia/') || in_array($timezone, ['UTC', 'Etc/UTC'], true))
            ->values();

        if (! $timezones->contains($this->timezone)) {
            $timezones->push($this->timezone);
        }

        return view('livewire.admin.settings', [
            'timezones' => $timezones,
            'dayLabels' => [
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
                7 => 'Minggu',
            ],
        ]);
    }
}
