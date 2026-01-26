<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\CreateApplication;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\EditApplication;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ListApplications;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\RelationManagers\EvaluationsRelationManager;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Schemas\ApplicationForm;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Tables\ApplicationsTable;
use UnitEnum;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|null|UnitEnum $navigationGroup = 'Recruitment';

    protected static ?string $slug = 'recruitment/applications';

    protected static ?string $recordTitleAttribute = 'tracking_code';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
            'create' => CreateApplication::route('/create'),
            'edit' => EditApplication::route('/{record}/edit'),
            'view' => ViewApplication::route('/{record}/view'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            EvaluationsRelationManager::class,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['tracking_code', 'candidate.user.name', 'candidate.user.email'];
    }

    public static function getModelLabel(): string
    {
        return __('applications::filament.resource.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('applications::filament.resource.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('applications::filament.resource.navigation_label');
    }
}
