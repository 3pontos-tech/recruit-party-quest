<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Resources\JobRequisitions;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\App\Filament\Resources\JobRequisitions\Pages\ListJobRequisitions;
use He4rt\App\Filament\Resources\JobRequisitions\Pages\ViewJobRequisition;
use He4rt\App\Filament\Resources\JobRequisitions\Schemas\JobRequisitionForm;
use He4rt\App\Filament\Resources\JobRequisitions\Schemas\JobRequisitionInfolist;
use He4rt\App\Filament\Resources\JobRequisitions\Tables\JobRequisitionsTable;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JobRequisitionResource extends Resource
{
    protected static ?string $model = JobRequisition::class;

    protected static bool $shouldSkipAuthorization = true;

    protected static bool $shouldRegisterNavigation = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return JobRequisitionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JobRequisitionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobRequisitionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJobRequisitions::route('/'),
            'view' => ViewJobRequisition::route('/{record}'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getModelLabel(): string
    {
        return __('recruitment::filament.requisition.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('recruitment::filament.requisition.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('recruitment::filament.requisition.navigation_label');
    }
}
