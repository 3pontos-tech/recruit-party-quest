<?php

declare(strict_types=1);

namespace He4rt\RepoAnalysis\Exceptions;

use RuntimeException;
use Throwable;

class GitHubException extends RuntimeException
{
    public function __construct(
        private readonly int $resetTime,
        string $message = 'An error occurred while communicating with the GitHub API.',
        int $code = 500,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function RateLimitExceeded(int $resetTime): self
    {
        return new self($resetTime, 'GitHub API rate limit exceeded. Please try again later.', 429);
    }

    public function getResetTime(): int
    {
        return $this->resetTime;
    }
}
