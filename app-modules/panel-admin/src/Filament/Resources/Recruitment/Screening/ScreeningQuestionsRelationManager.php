<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Recruitment\Screening;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\Models\ScreeningQuestion;
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
                Textarea::make('question_text')
                    ->label(__('screening::filament.question.fields.question_text'))
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('question_type')
                    ->label(__('screening::filament.question.fields.question_type'))
                    ->options(QuestionTypeEnum::class)
                    ->required()
                    ->live(),
                TextInput::make('display_order')
                    ->label(__('screening::filament.question.fields.display_order'))
                    ->numeric()
                    ->default(0)
                    ->required(),
                Repeater::make('choices')
                    ->label(__('screening::filament.question.fields.choices'))
                    ->schema([
                        TextInput::make('value')
                            ->label(__('screening::filament.question.fields.choice_value'))
                            ->required(),
                        TextInput::make('label')
                            ->label(__('screening::filament.question.fields.choice_label'))
                            ->required(),
                    ])
                    ->columns(2)
                    ->visible(fn ($get): bool => in_array($get('question_type'), [
                        QuestionTypeEnum::SingleChoice->value,
                        QuestionTypeEnum::MultipleChoice->value,
                    ]))
                    ->columnSpanFull(),
                Toggle::make('is_required')
                    ->label(__('screening::filament.question.fields.is_required'))
                    ->default(true),
                Toggle::make('is_knockout')
                    ->label(__('screening::filament.question.fields.is_knockout'))
                    ->default(false)
                    ->live(),
                KeyValue::make('knockout_criteria')
                    ->label(__('screening::filament.question.fields.knockout_criteria'))
                    ->visible(fn ($get): bool => $get('is_knockout') === true)
                    ->columnSpanFull(),
            ]);
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
