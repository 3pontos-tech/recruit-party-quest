<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('He4rt.Users.User.{id}', fn ($user, $id) => (string) $user->id === (string) $id);
Broadcast::channel('candidate-onboarding.resume.{userId}', fn ($userId) => true);
