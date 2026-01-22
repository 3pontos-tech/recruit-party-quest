<?php

declare(strict_types=1);

namespace He4rt\Screening\Filament\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\QuestionTypes\QuestionTypeRegistry;

final class ScreeningQuestionsFormSchema
{
    public static function make(): Repeater
    {
        return Repeater::make('screeningQuestions')
            ->relationship('screeningQuestions')
            ->label(__('screening::filament.form_schema.questions.label'))
            ->reorderable()
            ->orderColumn('display_order')
            ->collapsible()
            ->collapsed(fn ($record) => $record !== null)
            ->cloneable()
            ->itemLabel(fn (array $state): string => $state['question_text'] ?? __('screening::filament.form_schema.questions.new_question'))
            ->addActionLabel(__('screening::filament.form_schema.questions.add_question'))
            ->schema([
                Textarea::make('question_text')
                    ->label(__('screening::filament.question.fields.question_text'))
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),

                Select::make('question_type')
                    ->label(__('screening::filament.question.fields.question_type'))
                    ->options(QuestionTypeEnum::class)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($set): void {
                        $set('settings', null);
                    }),

                TextInput::make('display_order')
                    ->numeric()
                    ->default(0)
                    ->hidden(),

                Toggle::make('is_required')
                    ->label(__('screening::filament.question.fields.is_required'))
                    ->default(true)
                    ->inline(false),

                Toggle::make('is_knockout')
                    ->label(__('screening::filament.question.fields.is_knockout'))
                    ->helperText(__('screening::filament.question.fields.is_knockout_help'))
                    ->default(false)
                    ->live()
                    ->inline(false),

                Group::make()
                    ->schema(function ($get): array {
                        $typeValue = $get('question_type');

                        if ($typeValue === null) {
                            return [];
                        }

                        $type = $typeValue instanceof QuestionTypeEnum
                            ? $typeValue
                            : QuestionTypeEnum::tryFrom($typeValue);

                        return QuestionTypeRegistry::getSettingsSchema($type);
                    })
                    ->visible(fn ($get): bool => $get('question_type') !== null)
                    ->columnSpanFull(),

                KeyValue::make('knockout_criteria')
                    ->label(__('screening::filament.question.fields.knockout_criteria'))
                    ->helperText(__('screening::filament.question.fields.knockout_criteria_help'))
                    ->visible(fn ($get): bool => $get('is_knockout') === true)
                    ->columnSpanFull(),
            ])
            ->defaultItems(0)
            ->columnSpanFull();
    }
}
