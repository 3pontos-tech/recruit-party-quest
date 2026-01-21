<?php

declare(strict_types=1);

namespace He4rt\Candidates\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class ResumeAnalyzedEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(
        public readonly array $fields,
        public readonly string $userId
    ) {}

    public function broadcastOn()
    {
        return new Channel('fuedase');
    }

    public function broadcastAs(): string
    {
        return 'fodase';
    }
}
