<?php

declare(strict_types=1);

namespace He4rt\Applications\Events;

use He4rt\Applications\DTOs\ApplicationDTO;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class JobAppliedEvent
{
    use Dispatchable;

    public function __construct(
        public ApplicationDTO $dto,
    ) {}
}
