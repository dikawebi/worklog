<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Setting::current();

        $categories = [
            ['name' => 'Development', 'color' => '#0066FF', 'sort_order' => 1],
            ['name' => 'Support', 'color' => '#0284C7', 'sort_order' => 2],
            ['name' => 'Meeting', 'color' => '#D97706', 'sort_order' => 3],
            ['name' => 'Maintenance', 'color' => '#16A34A', 'sort_order' => 4],
            ['name' => 'Umum', 'color' => '#64748B', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['name' => $category['name']],
                $category + ['is_active' => true],
            );
        }

        $admin = User::query()->firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@borneoprima.com')],
            [
                'name' => env('ADMIN_NAME', 'Administrator IT'),
                'password' => env('ADMIN_PASSWORD') ?: 'password',
                'role' => UserRole::Admin,
                'position' => 'IT Administrator',
                'is_active' => true,
            ],
        );

        if ($admin->email_verified_at === null) {
            $admin->forceFill(['email_verified_at' => now()])->save();
        }

        if (app()->environment('local', 'testing')) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
