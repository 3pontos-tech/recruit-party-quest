<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use He4rt\Screening\Contracts\QuestionTypeContract;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\QuestionTypes\Settings\MultipleChoiceSettings;

/**
 * Multiple choice question type - checkboxes with multiple selections.
 */
final class MultipleChoiceType implements QuestionTypeContract
{
    public static function type(): QuestionTypeEnum
    {
        return QuestionTypeEnum::MultipleChoice;
    }

    public static function label(): string
    {
        return __('screening::question_types.multiple_choice.label');
    }

    public static function icon(): string
    {
        return 'heroicon-o-check';
    }

    public static function settingsSchema(): array
    {
        return [
            TextInput::make('settings.min_selections')
                ->label(__('screening::question_types.multiple_choice.settings.min_selections'))
                ->numeric()
                ->nullable()
                ->minValue(0)
                ->placeholder('0'),

            TextInput::make('settings.max_selections')
                ->label(__('screening::question_types.multiple_choice.settings.max_selections'))
                ->numeric()
                ->nullable()
                ->minValue(1)
                ->placeholder(__('screening::question_types.multiple_choice.settings.no_limit')),

            Repeater::make('settings.choices')
                ->label(__('screening::question_types.multiple_choice.settings.choices'))
                ->schema([
                    TextInput::make('value')
                        ->label(__('screening::question_types.multiple_choice.settings.choice_value'))
                        ->required(),
                    TextInput::make('label')
                        ->label(__('screening::question_types.multiple_choice.settings.choice_label'))
                        ->required(),
                ])
                ->columns(2)
                ->minItems(2)
                ->defaultItems(3)
                ->addActionLabel(__('screening::question_types.multiple_choice.settings.add_choice'))
                ->reorderable()
                ->columnSpanFull(),
        ];
    }

    public static function settingsClass(): string
    {
        return MultipleChoiceSettings::class;
    }

    public static function defaultSettings(): MultipleChoiceSettings
    {
        return new MultipleChoiceSettings();
    }

    public static function component(): string
    {
        return 'screening::questions.multiple-choice';
    }
}
