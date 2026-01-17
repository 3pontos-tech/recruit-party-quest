<?php

declare(strict_types=1);

namespace He4rt\Screening\Contracts;

use He4rt\Screening\Enums\QuestionTypeEnum;

/**
 * Contract for question type implementations.
 *
 * Each question type (YesNo, Text, FileUpload, etc.) implements this contract
 * to define its own settings schema for the admin form builder.
 */
interface QuestionTypeContract
{
    /**
     * Get the unique type identifier matching the enum value.
     */
    public static function type(): QuestionTypeEnum;

    /**
     * Get the human-readable label for this question type.
     */
    public static function label(): string;

    /**
     * Get the Heroicon name for this question type.
     */
    public static function icon(): string;

    /**
     * Get the Filament form fields for type-specific settings.
     *
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function settingsSchema(): array;
}
