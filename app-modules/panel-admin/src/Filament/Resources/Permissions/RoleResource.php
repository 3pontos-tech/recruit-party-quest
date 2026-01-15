<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Permissions;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Permissions\Pages\CreateRole;
use He4rt\Admin\Filament\Resources\Permissions\Pages\EditRole;
use He4rt\Admin\Filament\Resources\Permissions\Pages\ListRoles;
use He4rt\Admin\Filament\Resources\Permissions\Schemas\RoleForm;
use He4rt\Admin\Filament\Resources\Permissions\Schemas\RoleInfolist;
use He4rt\Admin\Filament\Resources\Permissions\Tables\RolesTable;
use He4rt\Permissions\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $slug = 'roles';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RoleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }
}
