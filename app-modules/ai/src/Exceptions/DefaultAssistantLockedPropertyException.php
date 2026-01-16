<?php

declare(strict_types=1);

namespace He4rt\Ai\Exceptions;

use Exception;

final class DefaultAssistantLockedPropertyException extends Exception
{
    public function __construct(
        string $property,
    ) {
        parent::__construct(sprintf('Cannot change the %s of the Institutional Assistant.', $property));
    }
}
