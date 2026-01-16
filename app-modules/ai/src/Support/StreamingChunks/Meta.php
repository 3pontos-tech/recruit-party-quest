<?php

declare(strict_types=1);

namespace He4rt\Ai\Support\StreamingChunks;

final readonly class Meta
{
    /**
     * @param  array<string, mixed>  $nextRequestOptions
     */
    public function __construct(
        public ?string $messageId,
        public array $nextRequestOptions,
    ) {}
}
