<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

final class PermissionSeeder extends Seeder
{
    /**
     * Seed roles and permissions required by the test suite.
     *
     * Called via migrate:fresh --seeder=PermissionSeeder in the test environment,
     * which runs before any test transaction opens, so the data persists across
     * every per-test rollback performed by LazilyRefreshDatabase.
     */
    public function run(): void
    {
        Artisan::call('sync:permissions');
    }
}
