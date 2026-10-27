<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk()
            ->assertSee('Jabatan')
            ->assertSee('Hubungi administrator untuk mengganti email.');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'position' => 'IT Support',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('IT Support', $user->position);
    }

    public function test_user_cannot_change_their_email_from_profile(): void
    {
        $user = User::factory()->create();
        $originalEmail = $user->email;

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'position' => $user->position,
                'email' => 'changed@example.com',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($originalEmail, $user->refresh()->email);
    }

    public function test_user_cannot_delete_their_own_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete('/profile', ['password' => 'password'])
            ->assertStatus(405);

        $this->assertNotNull($user->fresh());
    }
}
