<?php

declare(strict_types=1);

namespace He4rt\Ai\Listeners;

use He4rt\Ai\Events\AiThreadTrashed;
use He4rt\Ai\Models\AiMessage;

final class AiThreadCascadeDeleteAiMessages
{
    public function handle(AiThreadTrashed $event): void
    {
        $event->aiThread->messages()->lazyById()->each(
            fn (AiMessage $message) => $message->delete()
        );
    }
}
