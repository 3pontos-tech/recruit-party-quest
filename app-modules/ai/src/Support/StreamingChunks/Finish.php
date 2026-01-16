<?php

declare(strict_types=1);

namespace He4rt\Ai\Support\StreamingChunks;

use Carbon\CarbonInterface;

final readonly class Finish
{
    public function __construct(
        public bool $isIncomplete = false,
        public ?string $error = null,
        public ?CarbonInterface $rateLimitResetsAt = null,
    ) {}
}
