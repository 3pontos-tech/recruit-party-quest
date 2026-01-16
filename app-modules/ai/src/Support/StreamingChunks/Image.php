<?php

declare(strict_types=1);

namespace He4rt\Ai\Support\StreamingChunks;

final readonly class Image
{
    public function __construct(
        public string $content,
        public string $format,
    ) {}
}
