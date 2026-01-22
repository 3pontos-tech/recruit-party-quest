<?php

declare(strict_types=1);

namespace He4rt\Applications\Events;

use Carbon\CarbonInterface;
use He4rt\Applications\Models\Application;
use He4rt\Users\User;

final class OfferExtended
{
    public function __construct(
        public Application $application,
        public User $by,
        public ?string $offerAmount,
        public CarbonInterface $offerResponseDeadline
    ) {}
}
