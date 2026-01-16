<?php

declare(strict_types=1);

namespace He4rt\Ai\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface HasSteps
{
    public function steps(): HasMany;
}
