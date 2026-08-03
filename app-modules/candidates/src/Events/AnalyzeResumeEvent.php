<?php

declare(strict_types=1);

namespace He4rt\Candidates\Events;

use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\Enums\ResumeAnalyzeStatus;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

final class AnalyzeResumeEvent implements ShouldBroadcast
{
    use InteractsWithSockets;

    /**
     * @param  bool  $persistOnServer  Origem do upload: `true` no re-upload em Meu Perfil, onde
     *                                 a gravação é automática, e `false` no wizard, onde o
     *                                 resultado é oferecido para revisão. Carregar isso desde o
     *                                 dispatch é o que impede `StoreAnalyzedResume` de deduzir a
     *                                 origem por `is_onboarded`, que muda enquanto o job roda.
     */
    public function __construct(
        public readonly ResumeAnalyzeStatus $status,
        public readonly ?CandidateOnboardingDTO $fields,
        public readonly string $userId,
        public readonly ?string $message = null,
        public readonly ?int $code = null,
        public readonly bool $persistOnServer = false,
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
