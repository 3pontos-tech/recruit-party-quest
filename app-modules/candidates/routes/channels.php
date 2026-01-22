<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('candidate-onboarding.resume.{userId}', fn (Authenticatable $user, string $userId) => $user->getAuthIdentifier() === $userId);
