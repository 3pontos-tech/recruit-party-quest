<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Departments;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Departments\Pages\CreateDepartment;
use He4rt\Admin\Filament\Resources\Departments\Pages\EditDepartment;
use He4rt\Admin\Filament\Resources\Departments\Pages\ListDepartments;
use He4rt\Admin\Filament\Resources\Departments\Schemas\DepartmentForm;
use He4rt\Admin\Filament\Resources\Departments\Tables\DepartmentsTable;
use He4rt\Teams\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Override;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $slug = 'departments';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    public static function form(Schema $schema): Schema
    {
        return DepartmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartmentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDepartments::route('/'),
            'create' => CreateDepartment::route('/create'),
            'edit' => EditDepartment::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'team.name'];
    }

    public static function getModelLabel(): string
    {
        return __('teams::filament.department.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('teams::filament.department.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('teams::filament.department.navigation_label');
    }

    /**
     * @return Builder<Department>
     */
    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
