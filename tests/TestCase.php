<?php

declare(strict_types=1);

namespace Tests;

use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Traits\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Run PermissionSeeder during migrate:fresh so permissions are committed
     * to the SQLite in-memory connection before any test transaction opens.
     * This makes permission data survive all per-test rollbacks.
     */
    protected bool $seed = true;

    protected string $seeder = PermissionSeeder::class;
}
