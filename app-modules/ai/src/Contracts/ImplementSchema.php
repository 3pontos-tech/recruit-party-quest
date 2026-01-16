<?php

declare(strict_types=1);

namespace He4rt\Ai\Contracts;

use Prism\Prism\Contracts\Message;
use Prism\Prism\Contracts\Schema;

interface ImplementSchema
{
    public function toSchema(): Message|Schema;
}
