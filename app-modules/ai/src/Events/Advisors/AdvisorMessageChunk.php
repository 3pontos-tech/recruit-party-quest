<?php

declare(strict_types=1);

namespace He4rt\Ai\Events\Advisors;

use He4rt\Ai\Models\AiThread;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class AdvisorMessageChunk implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public AiThread $thread,
        public string $content,
    ) {}

    public function broadcastAs(): string
    {
        return 'advisor-message.chunk';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'content' => $this->content,
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
