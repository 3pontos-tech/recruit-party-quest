<?php

declare(strict_types=1);

namespace He4rt\Permissions\Commands\SyncPermissions;

use He4rt\Permissions\PermissionsEnum;

readonly class RolePermissions
{
    /**
     * @param  array<string, array<int, PermissionsEnum>>  $resources
     */
    public function __construct(
        public string $role,
        public array $resources,
    ) {}
}
