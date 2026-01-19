<?php

declare(strict_types=1);

namespace He4rt\Applications\Events;

use He4rt\Applications\Models\Application;
use He4rt\Users\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

final class ApplicationStatusChanged
{
    public Carbon $occurredAt;

    public function __construct(
        public Application $application,
        public string $fromStatus,
        public string $toStatus,
        public ?User $by = null,
        public array $meta = []
    ) {
        $this->occurredAt = Date::now();
    }
}
