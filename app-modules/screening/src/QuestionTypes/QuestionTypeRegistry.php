<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes;

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
        'file_upload' => FileUploadType::class,
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
}
