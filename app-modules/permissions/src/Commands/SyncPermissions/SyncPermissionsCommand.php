<?php

declare(strict_types=1);

namespace He4rt\Permissions\Commands\SyncPermissions;

use He4rt\Permissions\Permission;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Permissions\Role;
use He4rt\Permissions\Roles;
use He4rt\Users\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;

use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

class SyncPermissionsCommand extends Command
{
    protected $signature = 'sync:permissions';

    protected $description = 'Synchronize roles and permissions from the application models and configuration files';

    public function handle(PermissionRegistrar $permissionRegister): int
    {
        intro('Synchronizing roles and permissions...');

        // Reset cached roles and permissions
        spin(
            callback: fn () => $permissionRegister->forgetCachedPermissions(),
            message: 'Resetting cached roles and permissions...'
        );

        $models = $this->prepareModels();
        $rbacConfigs = $this->retrieveRolePermissionsFromModules($models);
        $permissions = $this->syncPermissions($models);

        $this->syncRoles();
        $this->syncRolesPermissions($rbacConfigs);
        $this->syncSuperAdminPermissions($permissions);

        outro('Roles and permissions synchronized successfully!');

        return self::SUCCESS;
    }

    public function syncRoles(): void
    {
        spin(
            callback: function (): void {
                foreach (Roles::cases() as $role) {
                    Role::query()->firstOrCreate([
                        'name' => $role->value,
                        'guard_name' => 'web',
                    ]);
                }
            },
            message: 'Syncing base roles...'
        );
    }

    /**
     * @param  Collection<int, RolePermissions>  $rbacConfigs
     */
    public function syncRolesPermissions(Collection $rbacConfigs): void
    {
        if ($rbacConfigs->isEmpty()) {
            return;
        }

        progress(
            label: 'Syncing RBAC configurations...',
            steps: $rbacConfigs->toArray(),
            callback: function (RolePermissions $rbacConfig): void {
                $role = Role::query()->firstOrCreate([
                    'name' => $rbacConfig->role,
                    'guard_name' => 'web',
                ]);

                $rolePermissions = [];
                foreach ($rbacConfig->resources as $resourceClass => $actions) {
                    foreach ($actions as $action) {
                        $rolePermissions[] = $action->buildPermissionFor($resourceClass);
                    }
                }

                if ($rolePermissions !== []) {
                    $role->syncPermissions($rolePermissions);
                }
            }
        );
    }

    /**
     * @return Collection<int, ModelPayload>
     */
    public function prepareModels(): Collection
    {
        return spin(
            callback: fn (): Collection => collect(Relation::morphMap())
                ->map(fn (string $modelPath, string|int $morphKey) => new ModelPayload(
                    name: $morphKey,
                    resource: $modelPath,
                    resourceGroup: (string) str($modelPath)->explode('\\')->offsetGet(1)
                ))
                ->values(),
            message: 'Preparing models from MorphMap...'
        );
    }

    /**
     * @param  Collection<int, ModelPayload>  $models
     * @return Collection<int, Permission>
     */
    public function syncPermissions(Collection $models): Collection
    {
        if ($models->isEmpty()) {
            return collect();
        }

        /** @var array<int, array<int, Permission>> $results */
        $results = progress(
            label: 'Generating permissions...',
            steps: $models->toArray(),
            callback: function (ModelPayload $modelPayload): array {
                $created = [];
                foreach (PermissionsEnum::cases() as $action) {
                    $permissionName = $action->buildPermissionFor($modelPayload->resource);
                    /** @var Permission $permission */
                    $permission = Permission::query()->firstOrCreate([
                        ...$modelPayload->toArray(),
                        'name' => $permissionName,
                        'guard_name' => 'web',
                        'action' => $action,
                    ]);
                    $created[] = $permission;
                }

                return $created;
            }
        );

        /** @var Collection<int, Permission> $permissions */
        $permissions = collect($results)->flatten();

        note(sprintf('Permissions generated: %s', $permissions->count()));

        return $permissions;
    }

    /**
     * @param  Collection<int, Permission>  $permissions
     */
    public function syncSuperAdminPermissions(Collection $permissions): void
    {
        $assigned = spin(
            callback: function () use ($permissions): bool {
                // Create Super Admin Role
                $superAdminRole = Role::query()->firstOrCreate([
                    'name' => Roles::SuperAdmin->value,
                    'guard_name' => 'web',
                ]);

                // Assign all permissions to Super Admin
                $superAdminRole->syncPermissions($permissions);

                $user = User::query()->where('email', 'admin@admin.com')->first();
                if ($user instanceof User) {
                    $user->assignRole(Roles::SuperAdmin->value);

                    return true;
                }

                return false;
            },
            message: 'Syncing Super Admin permissions...'
        );

        if (! $assigned) {
            warning('Default Super Admin user (admin@admin.com) not found.');
        }
    }

    /**
     * @param  Collection<int, ModelPayload>  $models
     * @return Collection<int, RolePermissions>
     */
    private function retrieveRolePermissionsFromModules(Collection $models): Collection
    {
        /** @var array<string, array<string, array<int, PermissionsEnum>>> $rbacConfigs */
        $rbacConfigs = [];
        $modulesWithRbac = [];

        spin(
            callback: function () use (&$rbacConfigs, &$modulesWithRbac): void {
                /** @var string $file */
                foreach (glob(modules_path('**/config/rbac.php')) ?: [] as $file) {
                    $config = require $file;
                    $rbacConfigs = array_merge_recursive($rbacConfigs, $config['permissions'] ?? []);

                    $moduleName = basename(dirname($file, 2));
                    $modulesWithRbac[mb_strtolower($moduleName)] = true;
                }
            },
            message: 'Scanning modules for RBAC configurations...'
        );

        $tableData = $models->map(function (ModelPayload $model) use ($modulesWithRbac): array {
            $hasRbac = isset($modulesWithRbac[mb_strtolower($model->resourceGroup)]);

            return [
                $model->name,
                $model->resource,
                $model->resourceGroup,
                $hasRbac ? '✓' : 'x',
            ];
        })->all();

        note('Models and RBAC Status:');
        table(['Name', 'Resource', 'Group', 'Has RBAC'], $tableData);

        return collect($rbacConfigs)->map(fn (array $resources, string $role) => new RolePermissions(
            role: $role,
            resources: $resources
        ))->values();
    }
}
