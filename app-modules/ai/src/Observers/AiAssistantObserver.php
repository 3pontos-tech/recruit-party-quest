<?php

declare(strict_types=1);

namespace He4rt\Ai\Observers;

use He4rt\Ai\Enums\AiAssistantApplication;
use He4rt\Ai\Exceptions\DefaultAssistantLockedPropertyException;
use He4rt\Ai\Models\AiAssistant;

final class AiAssistantObserver
{
    public function updating(AiAssistant $assistant): void
    {
        if ($assistant->application === AiAssistantApplication::PersonalAssistant && $assistant->is_default) {
            throw_if($assistant->isDirty('name'), DefaultAssistantLockedPropertyException::class, 'name');
            throw_if($assistant->isDirty('application'), DefaultAssistantLockedPropertyException::class, 'application');
        }
    }
}
