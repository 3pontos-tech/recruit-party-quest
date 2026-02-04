<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class GenerateJobRequisitionException extends Exception
{
    public static function somethingWentWrong(string $message = 'Something Went Wrong'): self
    {
        return new self(
            message: $message,
            code: Response::HTTP_SERVICE_UNAVAILABLE
        );
    }
}
