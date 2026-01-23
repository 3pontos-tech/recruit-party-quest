<?php

declare(strict_types=1);

use He4rt\Users\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(
    'candidate-onboarding.resume.{userId}',
    fn (User $user, string $userId) => $user->getKey() === $userId
);
