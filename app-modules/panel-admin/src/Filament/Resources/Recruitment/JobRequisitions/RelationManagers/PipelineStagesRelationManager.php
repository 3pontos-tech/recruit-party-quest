<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Recruitment\JobRequisitions\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use Illuminate\Database\Eloquent\Model;

class PipelineStagesRelationManager extends RelationManager
{
    protected static string $relationship = 'stages';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('recruitment::filament.relation_managers.stages.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('recruitment::filament.stage.fields.name'))
                    ->required()
                    ->maxLength(255),
                Select::make('stage_type')
                    ->label(__('recruitment::filament.stage.fields.stage_type'))
                    ->options(StageTypeEnum::class)
                    ->required(),
                TextInput::make('display_order')
                    ->label(__('recruitment::filament.stage.fields.display_order'))
                    ->numeric()
                    ->default(0)
                    ->required(),
                Textarea::make('description')
                    ->label(__('recruitment::filament.stage.fields.description'))
                    ->rows(3)
                    ->maxLength(1000),
                TextInput::make('expected_duration_days')
                    ->label(__('recruitment::filament.stage.fields.expected_duration_days'))
                    ->numeric()
                    ->minValue(1),
                Toggle::make('active')
                    ->label(__('recruitment::filament.stage.fields.active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('recruitment::filament.stage.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stage_type')
                    ->label(__('recruitment::filament.stage.fields.stage_type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('display_order')
                    ->label(__('recruitment::filament.stage.fields.display_order'))
                    ->sortable(),
                TextColumn::make('expected_duration_days')
                    ->label(__('recruitment::filament.stage.fields.expected_duration_days'))
                    ->sortable(),
                IconColumn::make('active')
                    ->label(__('recruitment::filament.stage.fields.active'))
                    ->boolean(),
            ])
            ->defaultSort('display_order')
            ->reorderable('display_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        /** @var JobRequisition $model */
                        $model = $this->getOwnerRecord();
                        $data['team_id'] = $model->team_id;

                        return $data;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
