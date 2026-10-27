<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WorklogAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_attach_a_photo_to_a_worklog_entry(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        Category::query()->create([
            'name' => 'Umum',
            'color' => '#64748B',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $photo = UploadedFile::fake()->image('server-room.jpg');

        Livewire::actingAs($user)
            ->test('worklog.today', ['date' => today()->format('Y-m-d')])
            ->set('entries.0.description', 'Dokumentasi server room.')
            ->set('attachments.0', [$photo])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Worklog berhasil disimpan.');

        $this->assertDatabaseHas('attachments', [
            'user_id' => $user->id,
            'original_name' => 'server-room.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        Storage::disk('local')->assertExists('worklogs/'.$user->id.'/'.basename(Attachment::query()->firstOrFail()->path));

        $this->actingAs($user)
            ->get(route('attachments.download', Attachment::query()->firstOrFail()))
            ->assertDownload('server-room.jpg');
    }

    public function test_total_attachments_per_entry_cannot_exceed_three(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        Category::query()->create([
            'name' => 'Umum',
            'color' => '#64748B',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $report = DailyReport::query()->create([
            'user_id' => $user->id,
            'report_date' => today()->format('Y-m-d'),
            'first_submitted_at' => now(),
        ]);
        $entry = $report->entries()->create([
            'description' => 'Dokumentasi lengkap.',
            'sort_order' => 0,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $entry->attachments()->create([
                'user_id' => $user->id,
                'disk' => 'local',
                'path' => 'worklogs/'.$user->id.'/existing-'.$i.'.jpg',
                'original_name' => 'existing-'.$i.'.jpg',
                'mime_type' => 'image/jpeg',
                'size' => 1024,
            ]);
        }

        Livewire::actingAs($user)
            ->test('worklog.today', ['date' => today()->format('Y-m-d')])
            ->set('attachments.0', [UploadedFile::fake()->image('fourth.jpg')])
            ->call('save')
            ->assertHasErrors('attachments.0');

        $this->assertSame(3, $entry->attachments()->count());
    }

    public function test_user_can_remove_an_existing_attachment(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $report = DailyReport::query()->create([
            'user_id' => $user->id,
            'report_date' => today()->format('Y-m-d'),
            'first_submitted_at' => now(),
        ]);
        $entry = $report->entries()->create([
            'description' => 'Dokumentasi dengan lampiran.',
            'sort_order' => 0,
        ]);
        $attachment = $entry->attachments()->create([
            'user_id' => $user->id,
            'disk' => 'local',
            'path' => 'worklogs/'.$user->id.'/removable.jpg',
            'original_name' => 'removable.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
        ]);

        Livewire::actingAs($user)
            ->test('worklog.today', ['date' => today()->format('Y-m-d')])
            ->call('removeAttachment', 0, $attachment->id)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('attachments', ['id' => $attachment->id]);
    }
}
