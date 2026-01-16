<?php

declare(strict_types=1);

namespace He4rt\Ai\Events;

use He4rt\Ai\Models\AiMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class AiMessageCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public AiMessage $aiMessage) {}
}
