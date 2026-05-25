<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
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
                    TextInput::make('label')
                        ->label(__('screening::question_types.multiple_choice.settings.choice_option'))
                        ->required()
                        ->distinct()
                        ->live(onBlur: true),
                ])
                ->columns(1)
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

    public static function knockoutCriteriaSchema(): array
    {
        return [
            Select::make('knockout_criteria.accepted')
                ->label(__('screening::filament.question.fields.knockout_accepted'))
                ->helperText(__('screening::filament.question.fields.knockout_accepted_multi_help').' '.__('screening::filament.question.fields.knockout_accepted_edit_warning'))
                ->options(fn (Get $get): array => self::acceptedOptions($get('settings.choices')))
                ->multiple()
                ->native(false)
                ->columnSpanFull()
                ->required(),
        ];
    }

    /**
     * Build the knockout "accepted answers" options from the choices state.
     *
     * Keyed by the choice value when present (persisted/normalized state),
     * otherwise the label (live admin state). label is required and distinct,
     * so keys are always non-empty and unique.
     *
     * @return array<string, string>
     */
    public static function acceptedOptions(mixed $choices): array
    {
        if (! is_array($choices)) {
            return [];
        }

        $options = [];

        foreach ($choices as $choice) {
            if (! is_array($choice)) {
                continue;
            }

            $label = mb_trim((string) ($choice['label'] ?? ''));
            $value = mb_trim((string) ($choice['value'] ?? ''));

            $key = $value !== '' ? $value : $label;

            if ($key === '') {
                continue;
            }

            $options[$key] = $label !== '' ? $label : $key;
        }

        return $options;
    }

    public static function evaluateKnockout(array $criteria, mixed $answer): bool
    {
        $accepted = $criteria['accepted'] ?? null;

        if (! is_array($accepted) || $accepted === []) {
            return true;
        }

        $selected = is_array($answer) ? $answer : [$answer];

        return array_intersect($selected, $accepted) !== [];
    }
}
