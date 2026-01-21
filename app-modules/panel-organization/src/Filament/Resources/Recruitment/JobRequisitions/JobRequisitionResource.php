<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\CreateJobRequisition;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\EditJobRequisition;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\Kanban\KanbanStages;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\ListJobRequisitions;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\RelationManagers\PipelineStagesRelationManager;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\RelationManagers\ScreeningQuestionsRelationManager;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Schemas\JobRequisitionForm;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Tables\JobRequisitionsTable;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Override;
use UnitEnum;

class JobRequisitionResource extends Resource
{
    protected static ?string $model = JobRequisition::class;

    protected static string|null|UnitEnum $navigationGroup = 'Recruitment';

    protected static ?string $slug = 'job-requisitions';

    protected static ?string $recordTitleAttribute = 'id';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Briefcase;

    protected static ?int $navigationSort = 10;

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
            'kanban' => KanbanStages::route('/{record}/kanban'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            PipelineStagesRelationManager::class,
            ScreeningQuestionsRelationManager::class,
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
