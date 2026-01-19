<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Recruitment\JobRequisitions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Recruitment\Stages\Schemas\StageForm;
use He4rt\Admin\Filament\Resources\Recruitment\Stages\StageResource;
use He4rt\Admin\Filament\Resources\Recruitment\Stages\Tables\StagesTable;
use Illuminate\Database\Eloquent\Model;

class PipelineStagesRelationManager extends RelationManager
{
    protected static string $relationship = 'stages';

    protected static ?string $relatedResource = StageResource::class;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('recruitment::filament.relation_managers.stages.title');
    }

    public function form(Schema $schema): Schema
    {
        return StageForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return StagesTable::configure($table);
    }

    protected static function getModelLabel(): ?string
    {
        return __('recruitment::filament.relation_managers.stages.label');
    }

    protected static function getPluralModelLabel(): ?string
    {
        return __('recruitment::filament.relation_managers.stages.plural_label');
    }
}
