<?php

declare(strict_types=1);

namespace He4rt\Ai\Contracts;

interface InteractsWithStep
{
    public function getStepOrder(): int;
}
