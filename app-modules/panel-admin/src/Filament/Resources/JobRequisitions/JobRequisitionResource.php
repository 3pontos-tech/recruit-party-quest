<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\JobRequisitions;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\JobRequisitions\Pages\CreateJobRequisition;
use He4rt\Admin\Filament\Resources\JobRequisitions\Pages\EditJobRequisition;
use He4rt\Admin\Filament\Resources\JobRequisitions\Pages\ListJobRequisitions;
use He4rt\Admin\Filament\Resources\JobRequisitions\Schemas\JobRequisitionForm;
use He4rt\Admin\Filament\Resources\JobRequisitions\Tables\JobRequisitionsTable;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Override;

class JobRequisitionResource extends Resource
{
    protected static ?string $model = JobRequisition::class;

    protected static ?string $slug = 'job-requisitions';

    protected static ?string $recordTitleAttribute = 'id';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    public static function form(Schema $schema): Schema
    {
        return JobRequisitionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobRequisitionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJobRequisitions::route('/'),
            'create' => CreateJobRequisition::route('/create'),
            'edit' => EditJobRequisition::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['team.name', 'department.name', 'hiringManager.name'];
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

    /**
     * @return Builder<JobRequisition>
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
