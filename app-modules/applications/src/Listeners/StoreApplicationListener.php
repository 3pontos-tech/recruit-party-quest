<?php

declare(strict_types=1);

namespace He4rt\Applications\Listeners;

use He4rt\Applications\Events\JobAppliedEvent;
use He4rt\Applications\Services\Applications\StoreApplication;

class StoreApplicationListener
{
    public function handle(JobAppliedEvent $event): void
    {
        resolve(StoreApplication::class)->execute($event->dto);
    }
}
