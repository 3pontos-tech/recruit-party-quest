<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('candidate-onboarding.resume.{userId}', fn ($userId) => auth()->user()->getKey() === $userId);

Broadcast::channel('fuedase', fn () => true);
