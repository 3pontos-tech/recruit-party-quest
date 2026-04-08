<?php

declare(strict_types=1);

namespace He4rt\Candidates\Jobs;

use He4rt\Candidates\AiAutocompleteInterface;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\Enums\ResumeAnalyzeStatus;
use He4rt\Candidates\Events\AnalyzeResumeEvent;
use He4rt\Candidates\Exceptions\OnboardingException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class AiAnalyzeResumeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 90;

    public function __construct(
        public string $temporaryFile,
        public string $userId
    ) {}

    public function tries(): int
    {
        return 2;
    }

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [15];
    }

    /** @return array<int, object> */
    public function middleware(): array
    {
        return [new RateLimited('gemini-cv-analysis')];
    }

    public function handle(): void
    {
        $this->processAnalysis();
    }

    public function failed(?Throwable $exception): void
    {
        logger()->error('CV analysis permanently failed after all retries', [
            'userId' => $this->userId,
            'exception' => $exception?->getMessage(),
            'class' => $exception instanceof Throwable ? $exception::class : null,
        ]);

        broadcast(new AnalyzeResumeEvent(
            status: ResumeAnalyzeStatus::Error,
            fields: null,
            userId: $this->userId,
            message: __('panel-app::pages/onboarding.notifications.rate_limit.body'),
            code: Response::HTTP_SERVICE_UNAVAILABLE,
        ));

        report_if($exception instanceof Throwable, $exception);
    }

    private function processAnalysis(): void
    {
        $temporaryFile = TemporaryUploadedFile::createFromLivewire($this->temporaryFile);

        try {
            /** @var CandidateOnboardingDTO $fields */
            $fields = resolve(AiAutocompleteInterface::class)->execute($temporaryFile);
            broadcast(new AnalyzeResumeEvent(ResumeAnalyzeStatus::Finished, $fields, $this->userId));
        } catch (OnboardingException $onboardingException) {
            if ($onboardingException->getCode() === Response::HTTP_UNPROCESSABLE_ENTITY) {
                logger()->warning('CV rejected as invalid document', [
                    'userId' => $this->userId,
                    'message' => $onboardingException->getMessage(),
                ]);

                broadcast(new AnalyzeResumeEvent(
                    status: ResumeAnalyzeStatus::Error,
                    fields: null,
                    userId: $this->userId,
                    message: $onboardingException->getMessage(),
                    code: $onboardingException->getCode(),
                ));

                return;
            }

            logger()->warning('Transient CV analysis failure, will retry', [
                'userId' => $this->userId,
                'attempt' => $this->attempts(),
                'error' => $onboardingException->getMessage(),
                'code' => $onboardingException->getCode(),
            ]);

            throw $onboardingException;
        }
    }
}
