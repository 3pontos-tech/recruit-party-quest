<?php

declare(strict_types=1);

namespace He4rt\Ai\Events\Advisors;

use Carbon\CarbonInterface;
use He4rt\Ai\Models\AiThread;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class AdvisorMessageFinished implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public AiThread $thread,
        public bool $isIncomplete = false,
        public ?string $error = null,
        public ?CarbonInterface $rateLimitResetsAt = null,
    ) {}

    public function broadcastAs(): string
    {
        return 'advisor-message.finished';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'is_incomplete' => $this->isIncomplete,
            'error' => $this->error,
            'rate_limit_resets_after_seconds' => $this->rateLimitResetsAt instanceof CarbonInterface ? (now()->diffInSeconds($this->rateLimitResetsAt) + 1) : null,
        ];
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('advisor-thread-'.$this->thread->getKey()),
        ];
    }
}
