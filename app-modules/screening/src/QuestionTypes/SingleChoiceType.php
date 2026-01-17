<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use He4rt\Screening\Contracts\QuestionTypeContract;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\QuestionTypes\Settings\SingleChoiceSettings;

/**
 * Single choice question type - radio buttons or dropdown with one selection.
 */
final class SingleChoiceType implements QuestionTypeContract
{
    public static function type(): QuestionTypeEnum
    {
        return QuestionTypeEnum::SingleChoice;
    }

    public static function label(): string
    {
        return __('screening::question_types.single_choice.label');
    }

    public static function icon(): string
    {
        return 'heroicon-o-list-bullet';
    }

    public static function settingsSchema(): array
    {
        return [
            Select::make('settings.layout')
                ->label(__('screening::question_types.single_choice.settings.layout'))
                ->options([
                    'radio' => __('screening::question_types.single_choice.settings.layout_radio'),
                    'dropdown' => __('screening::question_types.single_choice.settings.layout_dropdown'),
                ])
                ->default('radio'),

            Repeater::make('settings.choices')
                ->label(__('screening::question_types.single_choice.settings.choices'))
                ->schema([
                    TextInput::make('value')
                        ->label(__('screening::question_types.single_choice.settings.choice_value'))
                        ->required(),
                    TextInput::make('label')
                        ->label(__('screening::question_types.single_choice.settings.choice_label'))
                        ->required(),
                ])
                ->columns(2)
                ->minItems(2)
                ->defaultItems(2)
                ->addActionLabel(__('screening::question_types.single_choice.settings.add_choice'))
                ->reorderable()
                ->columnSpanFull(),
        ];
    }

    public static function settingsClass(): string
    {
        return SingleChoiceSettings::class;
    }

    public static function defaultSettings(): SingleChoiceSettings
    {
        return new SingleChoiceSettings();
    }
}
