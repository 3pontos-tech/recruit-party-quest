<?php

declare(strict_types=1);

namespace He4rt\Ai\Observers;

use He4rt\Ai\Models\Prompt;

final class PromptObserver
{
    public function creating(Prompt $prompt): void
    {
        $prompt->user()->associate(auth()->user());
    }
}
