<?php

declare(strict_types=1);

namespace He4rt\Candidates\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class OnboardingException extends Exception
{
    public static function invalidCv(): self
    {
        return new self(
            message: __('panel-app::pages/onboarding.notifications.is_not_cv.message'),
            code: Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    public static function rateLimiting(): self
    {
        return new self(
            message: __('panel-app::pages/onboarding.notifications.something_went_wrong.message'),
            code: Response::HTTP_SERVICE_UNAVAILABLE
        );
    }
}
