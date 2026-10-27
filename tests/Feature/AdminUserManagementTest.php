<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_mount_user_management(): void
    {
        $member = User::factory()->create();

        Livewire::actingAs($member)
            ->test('admin.users')
            ->assertForbidden();
    }

    public function test_admin_can_create_a_user(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('admin.users')
            ->call('create')
            ->set('name', 'Budi Santoso')
            ->set('email', 'budi@borneoprima.com')
            ->set('position', 'IT Support')
            ->set('role', UserRole::Member->value)
            ->set('isActive', true)
            ->set('password', 'password-baru')
            ->set('password_confirmation', 'password-baru')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Budi Santoso',
            'email' => 'budi@borneoprima.com',
            'position' => 'IT Support',
            'role' => UserRole::Member->value,
            'is_active' => true,
        ]);

        $this->assertNotNull(User::query()->where('email', 'budi@borneoprima.com')->value('email_verified_at'));
    }

    public function test_admin_can_update_role_and_deactivate_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        Livewire::actingAs($admin)
            ->test('admin.users')
            ->call('edit', $member->id)
            ->set('role', UserRole::Manager->value)
            ->set('isActive', false)
            ->call('save')
            ->assertHasNoErrors();

        $member->refresh();

        $this->assertSame(UserRole::Manager, $member->role);
        $this->assertFalse($member->is_active);
    }

    public function test_admin_cannot_deactivate_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('admin.users')
            ->call('toggleActive', $admin->id)
            ->assertHasErrors('isActive');

        $this->assertTrue($admin->refresh()->is_active);
    }

    public function test_admin_can_reset_another_user_password(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $component = Livewire::actingAs($admin)
            ->test('admin.users')
            ->call('resetPassword', $member->id)
            ->assertHasNoErrors();

        $temporaryPassword = $component->get('temporaryPassword');

        $this->assertNotEmpty($temporaryPassword);
        $this->assertSame($member->email, $component->get('temporaryPasswordEmail'));
        $this->assertTrue(Hash::check($temporaryPassword, $member->refresh()->password));
    }

    public function test_user_email_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        $existing = User::factory()->create();

        Livewire::actingAs($admin)
            ->test('admin.users')
            ->call('create')
            ->set('name', 'Duplikat')
            ->set('email', $existing->email)
            ->set('role', UserRole::Member->value)
            ->set('isActive', true)
            ->set('password', 'password-baru')
            ->set('password_confirmation', 'password-baru')
            ->call('save')
            ->assertHasErrors(['email' => 'unique']);
    }
}
