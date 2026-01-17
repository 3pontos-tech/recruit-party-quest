<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes;

use Filament\Forms\Components\TextInput;
use He4rt\Screening\Contracts\QuestionTypeContract;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\QuestionTypes\Settings\NumberSettings;

/**
 * Number question type - numeric input with min/max constraints.
 */
final class NumberType implements QuestionTypeContract
{
    public static function type(): QuestionTypeEnum
    {
        return QuestionTypeEnum::Number;
    }

    public static function label(): string
    {
        return __('screening::question_types.number.label');
    }

    public static function icon(): string
    {
        return 'heroicon-o-calculator';
    }

    public static function settingsSchema(): array
    {
        return [
            TextInput::make('settings.min')
                ->label(__('screening::question_types.number.settings.min'))
                ->numeric()
                ->placeholder('0'),

            TextInput::make('settings.max')
                ->label(__('screening::question_types.number.settings.max'))
                ->numeric()
                ->placeholder('100'),

            TextInput::make('settings.step')
                ->label(__('screening::question_types.number.settings.step'))
                ->numeric()
                ->minValue(0.01)
                ->placeholder('1'),

            TextInput::make('settings.prefix')
                ->label(__('screening::question_types.number.settings.prefix'))
                ->placeholder('$'),

            TextInput::make('settings.suffix')
                ->label(__('screening::question_types.number.settings.suffix'))
                ->placeholder('years'),
        ];
    }

    public static function settingsClass(): string
    {
        return NumberSettings::class;
    }

    public static function defaultSettings(): NumberSettings
    {
        return new NumberSettings();
    }
}
