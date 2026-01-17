<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes\Settings;

/**
 * Settings for Yes/No question type.
 *
 * This type has no additional settings - it's a simple boolean toggle.
 */
readonly class YesNoSettings
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [];
    }
}
