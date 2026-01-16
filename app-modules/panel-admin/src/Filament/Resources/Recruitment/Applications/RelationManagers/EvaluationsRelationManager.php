<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Recruitment\Applications\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use He4rt\Applications\Models\Application;
use He4rt\Feedback\Enums\EvaluationRatingEnum;
use He4rt\Feedback\Models\Evaluation;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Application getOwnerRecord()
 */
class EvaluationsRelationManager extends RelationManager
{
    protected static string $relationship = 'evaluations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('feedback::filament.relation_managers.evaluations.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('feedback::filament.evaluation.sections.context'))
                    ->columns(2)
                    ->schema([
                        Select::make('stage_id')
                            ->label(__('feedback::filament.evaluation.fields.stage'))
                            ->relationship(
                                name: 'stage',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('job_requisition_id', $this->getOwnerRecord()->requisition_id)
                            )
                            ->required()
                            ->preload()
                            ->searchable(),
                        Select::make('evaluator_id')
                            ->label(__('feedback::filament.evaluation.fields.evaluator'))
                            ->relationship(
                                name: 'evaluator',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->whereHas(
                                    'teams',
                                    fn ($q) => $q->whereKey($this->getOwnerRecord()->team_id)
                                )
                            )
                            ->default(fn () => auth()->id())
                            ->required()
                            ->preload()
                            ->searchable(),
                    ]),

                Section::make(__('feedback::filament.evaluation.sections.rating'))
                    ->schema([
                        Select::make('overall_rating')
                            ->label(__('feedback::filament.evaluation.fields.overall_rating'))
                            ->options(EvaluationRatingEnum::class)
                            ->required(),
                    ]),

                Section::make(__('feedback::filament.evaluation.sections.detailed_feedback'))
                    ->schema([
                        RichEditor::make('strengths')
                            ->label(__('feedback::filament.evaluation.fields.strengths'))
                            ->columnSpanFull(),
                        RichEditor::make('concerns')
                            ->label(__('feedback::filament.evaluation.fields.concerns'))
                            ->columnSpanFull(),
                        RichEditor::make('recommendation')
                            ->label(__('feedback::filament.evaluation.fields.recommendation'))
                            ->columnSpanFull(),
                        RichEditor::make('notes')
                            ->label(__('feedback::filament.evaluation.fields.notes'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('feedback::filament.evaluation.sections.criteria_scores'))
                    ->schema([
                        KeyValue::make('criteria_scores')
                            ->label(__('feedback::filament.evaluation.fields.criteria_scores'))
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make(__('feedback::filament.evaluation.sections.submission'))
                    ->schema([
                        DateTimePicker::make('submitted_at')
                            ->label(__('feedback::filament.evaluation.fields.submitted_at')),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('stage.name')
                    ->label(__('feedback::filament.evaluation.fields.stage'))
                    ->sortable(),
                TextColumn::make('evaluator.name')
                    ->label(__('feedback::filament.evaluation.fields.evaluator'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('overall_rating')
                    ->label(__('feedback::filament.evaluation.fields.overall_rating'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('recommendation')
                    ->label(__('feedback::filament.evaluation.fields.recommendation'))
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('submitted_at')
                    ->label(__('feedback::filament.evaluation.fields.submitted_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('feedback::filament.evaluation.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        /** @var Evaluation $model */
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
        return __('feedback::filament.relation_managers.evaluations.label');
    }

    protected static function getPluralModelLabel(): ?string
    {
        return __('feedback::filament.relation_managers.evaluations.plural_label');
    }
}
