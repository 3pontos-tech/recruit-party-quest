<?php

declare(strict_types=1);

namespace He4rt\Screening\Listeners;

use He4rt\Screening\Actions\ScreeningResponse\EvaluateScreeningResponses;
use He4rt\Screening\Actions\ScreeningResponse\StoreScreeningResponse;
use He4rt\Screening\Events\ScreeningResponsesSubmitted;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class StoreAndEvaluateScreeningResponses
{
    public function __construct(
        private StoreScreeningResponse $store,
        private EvaluateScreeningResponses $evaluate,
    ) {}

    public function handle(ScreeningResponsesSubmitted $event): void
    {
        $this->store->execute($event->responses);

        // Best-effort: falha de avaliação nunca pode quebrar a submissão do candidato.
        try {
            $this->evaluate->execute($event->application);
        } catch (Throwable $throwable) {
            Log::error('Screening evaluation failed after application submission', [
                'application_id' => $event->application->getKey(),
                'exception' => $throwable,
            ]);
        }
    }
}
