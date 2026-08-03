<?php

declare(strict_types=1);

namespace He4rt\Candidates\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Every model has an open circuit breaker, so no request was sent to the provider.
 *
 * This is terminal for the current analysis: the breaker TTL outlives the queue backoff,
 * so retrying would only skip every model again without ever reaching the provider.
 */
final class ProvidersUnavailableException extends OnboardingException
{
    public static function make(?Throwable $previous = null): self
    {
        return new self(
            message: __('panel-app::pages/onboarding.notifications.rate_limit.body'),
            code: Response::HTTP_SERVICE_UNAVAILABLE,
            previous: $previous,
        );
    }
}
