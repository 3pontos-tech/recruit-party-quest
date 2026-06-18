<?php

declare(strict_types=1);

namespace He4rt\Screening\Events;

use He4rt\Applications\Models\Application;
use He4rt\Screening\Collections\ScreeningResponseCollection;

final class ScreeningResponsesSubmitted
{
    public function __construct(
        public Application $application,
        public ScreeningResponseCollection $responses,
    ) {}
}
