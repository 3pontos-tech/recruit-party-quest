<?php

declare(strict_types=1);

namespace He4rt\Permissions;

use He4rt\Permissions\Database\Factories\PermissionFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as BasePermission;

/**
 * @property string $guard_name
 * @property string $name
 * @property string $resource
 * @property string $action
 * @property string $resource_group
 * @property-read string $formatted_name
 * @property-read string $resource_model
 */
class Permission extends BasePermission
{
    /** @use HasFactory<PermissionFactory> */
    use HasFactory;

    /** @return Attribute<string, void> */
    protected function resourceModel(): Attribute
    {
        return Attribute::make(
            get: fn () => str($this->resource)->explode('\\')->last()
        );
    }

    /** @return Attribute<string, void> */
    protected function formattedName(): Attribute
    {
        return Attribute::make(
            get: fn () => sprintf(
                '%s-%s-%s-%s', $this->resource_group, $this->resource_model, $this->action, $this->name
            )
        );
    }
}
