<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('--- Seeding Super Admin ---');

        $user = User::updateOrCreate(
            ['email' => 'devimakeup.wo@gmail.com'],
            [
                'full_name' => 'Super Admin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'username' => 'superadmin',
                'password' => Hash::make('@Admin123'),
                'email_verified_at' => now(),
                'avatar_url' => 'https://i.pravatar.cc/300?u=superadmin',
            ]
        );

        if (! $user->hasRole('super_admin')) {
            $user->assignRole('super_admin');
        }

        $this->command->line("  <info>✓</info> Super Admin Created: {$user->email}");
        $this->command->info('--- Super Admin Seeding Complete ---');
    }
}
