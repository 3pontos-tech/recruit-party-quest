<?php

declare(strict_types=1);

namespace He4rt\RepoAnalysis\Events;

use He4rt\RepoAnalysis\Models\RepositoryAnalysis;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class AnalysisStatusChanged implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly RepositoryAnalysis $analysis
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('repo-analysis.'.$this->analysis->candidate->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return $this->analysis->status->value;
    }

    /** @return array<string, string> */
    public function broadcastWith(): array
    {
        return [
            'analysis_id' => $this->analysis->id,
            'repo_name' => $this->analysis->repo_name,
            'status' => $this->analysis->status->value,
        ];
    }
}
