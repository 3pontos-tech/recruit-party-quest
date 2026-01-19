<?php

declare(strict_types=1);

namespace Database\Seeders;

use He4rt\Permissions\Roles;
use He4rt\Users\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->syncPermissions();

        if (! app()->isProduction()) {
            $this->spawnAdminUser();
            $this->call(DevelopmentSeeder::class);
        }

        $this->command->newLine();
        $this->command->info('Database seeding completed successfully. Have fun!');
    }

    public function spawnAdminUser(): void
    {
        $this->command->warn('Creating admin user...');

        User::factory()
            ->admin()
            ->create()
            ->assignRole(Roles::SuperAdmin);

        $this->command->info('Admin user created successfully.');
    }

    private function syncPermissions(): void
    {
        $this->command->warn('Syncing permissions...');
        Artisan::call('sync:permissions');
        $this->command->info('Permissions synced successfully.');
    }
}
