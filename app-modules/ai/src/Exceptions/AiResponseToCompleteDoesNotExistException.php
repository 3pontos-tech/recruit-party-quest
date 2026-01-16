<?php

declare(strict_types=1);

namespace He4rt\Ai\Exceptions;

use Exception;

final class AiResponseToCompleteDoesNotExistException extends Exception
{
    public function __construct()
    {
        parent::__construct('The AI response to finish completing does not exist.');
    }
}
