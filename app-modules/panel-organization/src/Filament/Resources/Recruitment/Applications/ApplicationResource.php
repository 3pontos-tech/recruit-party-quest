<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ListApplications;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Tables\ApplicationsTable;
use UnitEnum;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $slug = 'recruitment/applications';

    protected static ?string $recordTitleAttribute = 'tracking_code';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('panel-organization::filament.group.recruitment');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
            'view' => ViewApplication::route('/{record}/view'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['tracking_code', 'candidate.user.name', 'candidate.user.email'];
    }

    public static function getModelLabel(): string
    {
        return __('panel-organization::filament.applications.resource.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel-organization::filament.applications.resource.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel-organization::filament.applications.resource.navigation_label');
    }
}
