<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
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
                    TextInput::make('label')
                        ->label(__('screening::question_types.single_choice.settings.choice_option'))
                        ->required()
                        ->distinct()
                        ->live(onBlur: true),
                ])
                ->columns(1)
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

    public static function component(): string
    {
        return 'screening::questions.single-choice';
    }

    public static function knockoutCriteriaSchema(): array
    {
        return [
            Select::make('knockout_criteria.accepted')
                ->label(__('screening::filament.question.fields.knockout_accepted'))
                ->helperText(__('screening::filament.question.fields.knockout_accepted_edit_warning'))
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
     * Keyed by the choice identity that the candidate submits: the choice value
     * when present (persisted/normalized state), otherwise the label (live admin
     * state, where only the single "Option" field exists). label is required and
     * distinct, so keys are always non-empty and unique — no duplicate-key
     * checkbox state bleed.
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

        return in_array($answer, $accepted, true);
    }
}
