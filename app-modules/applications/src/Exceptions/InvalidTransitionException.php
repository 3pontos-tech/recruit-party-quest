<?php

declare(strict_types=1);

namespace He4rt\Applications\Exceptions;

use Exception;
use He4rt\Applications\Enums\ApplicationStatusEnum;

final class InvalidTransitionException extends Exception
{
    public static function notAllowed(ApplicationStatusEnum $status): self
    {
        return new self(sprintf('Transition from New to %s is not allowed', $status->getLabel()), 500);
    }

    public static function invalidTarget(string $status): self
    {
        return new self(sprintf('Invalid target status: %s', $status), 400);
    }
}
