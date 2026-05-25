<?php

declare(strict_types=1);

namespace He4rt\Screening\Filament\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\QuestionTypes\QuestionTypeRegistry;
use Illuminate\Database\Eloquent\Model;

class ScreeningQuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'screeningQuestions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('screening::filament.relation_managers.questions.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('screening::filament.question.sections.question.title'))
                    ->description(__('screening::filament.question.sections.question.description'))
                    ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                    ->schema([
                        Textarea::make('question_text')
                            ->label(__('screening::filament.question.fields.question_text'))
                            ->placeholder(__('screening::filament.question.fields.question_text_placeholder'))
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('question_type')
                            ->label(__('screening::filament.question.fields.question_type'))
                            ->helperText(__('screening::filament.question.fields.question_type_help'))
                            ->options(QuestionTypeEnum::class)
                            ->native(false)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state): void {
                                if ($state === null) {
                                    $set('settings', null);

                                    return;
                                }

                                $type = $state instanceof QuestionTypeEnum
                                    ? $state
                                    : QuestionTypeEnum::tryFrom($state);

                                if ($type === null) {
                                    $set('settings', null);

                                    return;
                                }

                                $typeClass = QuestionTypeRegistry::get($type);
                                $set('settings', $typeClass::defaultSettings()->toArray());
                            }),
                        Hidden::make('display_order')
                            ->default(0),
                    ]),

                Section::make(__('screening::filament.question.sections.answer.title'))
                    ->description(__('screening::filament.question.sections.answer.description'))
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->visible(fn (Get $get): bool => $get('question_type') !== null)
                    ->schema([
                        ...QuestionTypeRegistry::settingsSchemaComponents(),
                        Toggle::make('is_required')
                            ->label(__('screening::filament.question.fields.is_required'))
                            ->helperText(__('screening::filament.question.fields.is_required_help'))
                            ->default(true)
                            ->inline(false),
                    ]),

                Section::make(__('screening::filament.question.sections.knockout.title'))
                    ->description(__('screening::filament.question.sections.knockout.description'))
                    ->icon(Heroicon::OutlinedShieldExclamation)
                    ->schema([
                        Toggle::make('is_knockout')
                            ->label(__('screening::filament.question.fields.is_knockout'))
                            ->helperText(__('screening::filament.question.fields.is_knockout_help'))
                            ->default(false)
                            ->live()
                            ->inline(false),
                        ...QuestionTypeRegistry::knockoutSchemaComponents(),
                    ]),
            ])->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question_text')
                    ->label(__('screening::filament.question.fields.question_text'))
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('question_type')
                    ->label(__('screening::filament.question.fields.question_type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('display_order')
                    ->label(__('screening::filament.question.fields.display_order'))
                    ->sortable(),
                IconColumn::make('is_required')
                    ->label(__('screening::filament.question.fields.is_required'))
                    ->boolean(),
                IconColumn::make('is_knockout')
                    ->label(__('screening::filament.question.fields.is_knockout'))
                    ->boolean()
                    ->trueColor('danger'),
                TextColumn::make('responses_count')
                    ->label(__('screening::filament.question.fields.responses_count'))
                    ->counts('responses')
                    ->badge(),
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
                        /** @var ScreeningQuestion $model */
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
        return __('screening::filament.relation_managers.questions.label');
    }

    protected static function getPluralModelLabel(): ?string
    {
        return __('screening::filament.relation_managers.questions.plural_label');
    }
}
