<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Category;
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
}
