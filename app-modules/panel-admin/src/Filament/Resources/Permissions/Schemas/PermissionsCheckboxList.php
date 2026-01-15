<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\CheckboxList;

class PermissionsCheckboxList extends CheckboxList
{
    protected string $view = 'panel-admin::components.permissions-checkbox-list';
}
