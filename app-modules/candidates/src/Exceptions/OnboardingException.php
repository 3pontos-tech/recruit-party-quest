<?php

declare(strict_types=1);

namespace He4rt\Candidates\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class OnboardingException extends Exception
{
    public static function invalidCv(string $message = 'Arquivo enviado não é um currículo.'): self
    {
        return new self(
            message: $message,
            code: Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
