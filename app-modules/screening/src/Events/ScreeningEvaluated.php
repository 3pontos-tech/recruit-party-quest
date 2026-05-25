<?php

declare(strict_types=1);

namespace He4rt\Screening\Events;

use He4rt\Applications\Models\Application;

final class ScreeningEvaluated
{
    public function __construct(
        public Application $application,
        public bool $anyKnockoutFailed,
        public bool $hadKnockoutCriteria,
    ) {}
}
