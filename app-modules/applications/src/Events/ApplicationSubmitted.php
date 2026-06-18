<?php

declare(strict_types=1);

namespace He4rt\Applications\Events;

use He4rt\Applications\Models\Application;

final class ApplicationSubmitted
{
    public function __construct(public Application $application) {}
}
