<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use He4rt\Screening\Contracts\QuestionTypeContract;
use He4rt\Screening\Enums\QuestionTypeEnum;
use RuntimeException;

/**
 * Registry for discovering and retrieving question type implementations.
 */
final class QuestionTypeRegistry
{
    /**
     * Map of enum values to their implementing classes.
     *
     * @var array<string, class-string<QuestionTypeContract>>
     */
    private static array $types = [
        'yes_no' => YesNoType::class,
        'text' => TextType::class,
        'number' => NumberType::class,
        'single_choice' => SingleChoiceType::class,
        'multiple_choice' => MultipleChoiceType::class,
        //        'file_upload' => FileUploadType::class,
    ];

    /**
     * Get all registered question type classes.
     *
     * @return array<class-string<QuestionTypeContract>>
     */
    public static function all(): array
    {
        return array_values(self::$types);
    }

    /**
     * Get a question type class by its enum.
     */
    public static function get(QuestionTypeEnum $type): string
    {
        return self::$types[$type->value]
            ?? throw new RuntimeException('Unknown question type: '.$type->value);
    }

    /**
     * Get the settings schema for a question type.
     *
     * @return array<int, mixed>
     */
    public static function getSettingsSchema(?QuestionTypeEnum $type): array
    {
        if (! $type instanceof QuestionTypeEnum) {
            return [];
        }

        $class = self::get($type);

        return $class::settingsSchema();
    }

    /**
     * Stable per-type settings groups.
     *
     * Every type's settings fields are built once at form-build time and
     * toggled with visible() — instead of a single Group whose schema is
     * rebuilt by a reactive closure. Recreating stateful fields per render
     * breaks the Livewire/Alpine DOM binding of widgets (Select, Repeater);
     * stable components with visibility do not.
     *
     * @return array<int, Group>
     */
    public static function settingsSchemaComponents(): array
    {
        $components = [];

        foreach (self::$types as $value => $class) {
            $type = QuestionTypeEnum::from($value);

            $components[] = Group::make()
                ->key('settings-fields-'.$value)
                ->schema($class::settingsSchema())
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn (Get $get): bool => self::resolveType($get('question_type')) === $type);
        }

        return $components;
    }

    /**
     * Stable per-type knockout criteria groups.
     *
     * Same rationale as settingsSchemaComponents(): stable components gated by
     * visible(), never recreated by a reactive schema closure.
     *
     * @return array<int, Group>
     */
    public static function knockoutSchemaComponents(): array
    {
        $components = [];

        foreach (self::$types as $value => $class) {
            $type = QuestionTypeEnum::from($value);

            $schema = $class::knockoutCriteriaSchema();

            if ($schema === []) {
                $schema = [
                    TextEntry::make('knockout_unsupported_'.$value)
                        ->hiddenLabel()
                        ->state(__('screening::filament.question.sections.knockout.unsupported'))
                        ->color('warning'),
                ];
            }

            $components[] = Group::make()
                ->key('knockout-fields-'.$value)
                ->schema($schema)
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn (Get $get): bool => $get('is_knockout') === true
                    && self::resolveType($get('question_type')) === $type);
        }

        return $components;
    }

    private static function resolveType(mixed $value): ?QuestionTypeEnum
    {
        if ($value instanceof QuestionTypeEnum) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return QuestionTypeEnum::tryFrom((string) $value);
    }
}
