<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Stages;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Recruitment\Screening\ScreeningQuestionsRelationManager;
use He4rt\Admin\Filament\Resources\Stages\Pages\EditStage;
use He4rt\Admin\Filament\Resources\Stages\Pages\ListStages;
use He4rt\Admin\Filament\Resources\Stages\Schemas\StageForm;
use He4rt\Admin\Filament\Resources\Stages\Tables\StagesTable;
use He4rt\Recruitment\Stages\Models\Stage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StageResource extends Resource
{
    protected static ?string $model = Stage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return StageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ScreeningQuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStages::route('/'),
            'edit' => EditStage::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
