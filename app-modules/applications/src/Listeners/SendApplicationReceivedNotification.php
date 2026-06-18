<?php

declare(strict_types=1);

namespace He4rt\Applications\Listeners;

use He4rt\Applications\Events\ApplicationSubmitted;
use He4rt\Applications\Notifications\ApplicationReceivedNotification;

final class SendApplicationReceivedNotification
{
    public function handle(ApplicationSubmitted $event): void
    {
        $user = $event->application->candidate?->user;

        if ($user === null) {
            return;
        }

        $user->notify(new ApplicationReceivedNotification($event->application));
    }
}
