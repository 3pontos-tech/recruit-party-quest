<?php

declare(strict_types=1);

namespace He4rt\Applications\Exceptions;

use Exception;

final class MissingTransitionDataException extends Exception
{
    public static function forField(string $field): self
    {
        return new self(sprintf('Field %s is required', $field), 422);
    }
}
