<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Users;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Users\Pages\CreateUser;
use He4rt\Admin\Filament\Resources\Users\Pages\EditUser;
use He4rt\Admin\Filament\Resources\Users\Pages\ListUsers;
use He4rt\Admin\Filament\Resources\Users\Schemas\UserForm;
use He4rt\Admin\Filament\Resources\Users\Schemas\UserInfolist;
use He4rt\Admin\Filament\Resources\Users\Tables\UsersTable;
use He4rt\Users\User;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'users';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getModelLabel(): string
    {
        return __('users::labels.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('users::labels.plural');
    }
}
