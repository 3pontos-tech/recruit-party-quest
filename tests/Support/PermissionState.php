<?php

declare(strict_types=1);

namespace Tests\Support;

/**
 * Tracks whether permissions have been synchronized in the current test process.
 *
 * Used by the global Pest beforeAll hook to ensure sync:permissions runs only
 * once per process rather than once per test, improving suite performance.
 */
final class PermissionState
{
    public static bool $synced = false;
}
