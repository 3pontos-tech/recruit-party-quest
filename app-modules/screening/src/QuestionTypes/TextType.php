<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use He4rt\Screening\Contracts\QuestionTypeContract;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\QuestionTypes\Settings\TextSettings;

/**
 * Text question type - free-form text input.
 */
final class TextType implements QuestionTypeContract
{
    public static function type(): QuestionTypeEnum
    {
        return QuestionTypeEnum::Text;
    }

    public static function label(): string
    {
        return __('screening::question_types.text.label');
    }

    public static function icon(): string
    {
        return 'heroicon-o-document-text';
    }

    public static function settingsSchema(): array
    {
        return [
            TextInput::make('settings.max_length')
                ->label(__('screening::question_types.text.settings.max_length'))
                ->numeric()
                ->minValue(1)
                ->maxValue(10000)
                ->placeholder('500'),

            Toggle::make('settings.multiline')
                ->label(__('screening::question_types.text.settings.multiline'))
                ->helperText(__('screening::question_types.text.settings.multiline_help'))
                ->default(false),

            TextInput::make('settings.placeholder')
                ->label(__('screening::question_types.text.settings.placeholder'))
                ->placeholder(__('screening::question_types.text.settings.placeholder_example')),
        ];
    }

    public static function settingsClass(): string
    {
        return TextSettings::class;
    }

    public static function defaultSettings(): TextSettings
    {
        return new TextSettings();
    }
}
