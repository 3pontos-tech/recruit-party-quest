<?php

declare(strict_types=1);

namespace He4rt\Candidates\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ResumeAnalyzedEvent implements ShouldBroadcast
{
    use InteractsWithSockets;

    public function __construct(
        public readonly array $fields,
        public readonly string $userId
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('candidate-onboarding.resume.'.$this->userId);
    }
}
