<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes;

use He4rt\Screening\Contracts\QuestionTypeContract;
use He4rt\Screening\Enums\QuestionTypeEnum;

/**
 * Yes/No question type - simple boolean toggle.
 */
final class YesNoType implements QuestionTypeContract
{
    public static function type(): QuestionTypeEnum
    {
        return QuestionTypeEnum::YesNo;
    }

    public static function label(): string
    {
        return __('screening::question_types.yes_no.label');
    }

    public static function icon(): string
    {
        return 'heroicon-o-check-circle';
    }

    /**
     * Yes/No has no additional settings - it's just a simple toggle.
     */
    public static function settingsSchema(): array
    {
        return [];
    }
}
