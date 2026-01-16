<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Applications;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Applications\Pages\CreateApplication;
use He4rt\Admin\Filament\Resources\Applications\Pages\EditApplication;
use He4rt\Admin\Filament\Resources\Applications\Pages\ListApplications;
use He4rt\Admin\Filament\Resources\Applications\Schemas\ApplicationForm;
use He4rt\Admin\Filament\Resources\Applications\Tables\ApplicationsTable;
use He4rt\Applications\Models\Application;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $slug = 'applications';

    protected static ?string $recordTitleAttribute = 'tracking_code';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

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
