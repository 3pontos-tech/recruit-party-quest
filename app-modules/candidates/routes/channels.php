<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('candidate-onboarding.resume.{userId}', fn ($userID) => true);
