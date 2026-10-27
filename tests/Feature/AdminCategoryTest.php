<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_manage_categories(): void
    {
        $member = User::factory()->create();

        Livewire::actingAs($member)
            ->test('admin.categories')
            ->assertForbidden();
    }

    public function test_admin_can_create_a_category(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('admin.categories')
            ->call('create')
            ->set('name', 'Infrastruktur')
            ->set('color', '#123ABC')
            ->set('sortOrder', 6)
            ->set('isActive', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'name' => 'Infrastruktur',
            'color' => '#123ABC',
            'sort_order' => 6,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_deactivate_a_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::query()->create([
            'name' => 'Support',
            'color' => '#0284C7',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Livewire::actingAs($admin)
            ->test('admin.categories')
            ->call('toggleActive', $category->id)
            ->assertHasNoErrors();

        $this->assertFalse($category->refresh()->is_active);
    }

    public function test_umum_category_cannot_be_deactivated(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::query()->create([
            'name' => 'Umum',
            'color' => '#64748B',
            'is_active' => true,
            'sort_order' => 5,
        ]);

        Livewire::actingAs($admin)
            ->test('admin.categories')
            ->call('toggleActive', $category->id)
            ->assertHasErrors('category');

        $this->assertTrue($category->refresh()->is_active);
    }

    public function test_category_name_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Category::query()->create([
            'name' => 'Meeting',
            'color' => '#D97706',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        Livewire::actingAs($admin)
            ->test('admin.categories')
            ->call('create')
            ->set('name', 'Meeting')
            ->set('color', '#D97706')
            ->set('sortOrder', 3)
            ->set('isActive', true)
            ->call('save')
            ->assertHasErrors(['name' => 'unique']);
    }
}
