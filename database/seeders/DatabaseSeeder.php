<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);

        $adminUser = User::factory()->create([
            'username' => 'admin',
            'email'    => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);

        $adminUser->assignRole('admin');
    }
}
