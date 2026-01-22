<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

final class AnalyzeResumeEvent implements ShouldBroadcast
{
    use InteractsWithSockets;

    public function __construct(
        public readonly ResumeAnalyzeStatus $status,
        public readonly array $fields,
        public readonly string $userId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('candidate-onboarding.resume.'.$this->userId),
            new Channel('candidate'),
        ];
    }

    public function broadcastAs(): string
    {
        return $this->status->value;
    }
}
