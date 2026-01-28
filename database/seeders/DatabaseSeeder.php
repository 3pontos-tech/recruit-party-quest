<?php

declare(strict_types=1);

namespace Database\Seeders;

use He4rt\Permissions\Roles;
use He4rt\Teams\Team;
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
            $adminData = $this->spawnAdminUser();
            $this->call(DevelopmentSeeder::class, false, ['adminData' => $adminData]);
        }

        $this->command->newLine();
        $this->command->info('Database seeding completed successfully. Have fun!');
    }

    public function spawnAdminUser(): array
    {
        $this->command->warn('Creating admin user...');

        $admin = User::factory()
            ->admin()
            ->create();

        $admin->assignRole(Roles::SuperAdmin);

        $team = Team::factory()
            ->hasDepartments(2)
            ->create([
                'name' => '3Pontos',
                'slug' => '3pontos',
                'owner_id' => $admin->getKey(),
            ]);

        $team->members()->attach($admin);

        $departments = $team->departments;

        $this->command->info('Admin user created successfully.');

        return [
            'admin' => $admin,
            'team' => $team,
            'departments' => $departments,
        ];
    }

    private function syncPermissions(): void
    {
        $this->command->warn('Syncing permissions...');
        Artisan::call('sync:permissions');
        $this->command->info('Permissions synced successfully.');
    }
}
