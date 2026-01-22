<?php

declare(strict_types=1);

namespace He4rt\Applications\Events;

use Carbon\CarbonInterface;
use He4rt\Applications\Models\Application;
use He4rt\Users\User;
use Illuminate\Support\Facades\Date;

final class ApplicationStatusChanged
{
    public CarbonInterface $occurredAt;

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    public function __construct(
        public Application $application,
        public string $fromStatus,
        public string $toStatus,
        public User $by,
        public array $meta = []
    ) {
        $this->occurredAt = Date::now();
    }
}
