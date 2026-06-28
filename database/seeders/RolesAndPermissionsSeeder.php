<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('--- Seeding Roles & Permissions ---');

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        Role::firstOrCreate(['name' => 'super_admin']);
        $this->command->line('  <info>✓</info> Role: super_admin created');

        Role::firstOrCreate(['name' => 'user']);
        $this->command->line('  <info>✓</info> Role: user created');

        Role::firstOrCreate(['name' => 'customer']);
        $this->command->line('  <info>✓</info> Role: customer created');

        $this->command->info('--- Roles & Permissions Seeding Complete ---');
    }
}
