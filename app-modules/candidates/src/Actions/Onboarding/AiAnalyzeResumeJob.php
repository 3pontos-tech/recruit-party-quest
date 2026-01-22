<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

use He4rt\Candidates\AiAutocompleteInterface;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class AiAnalyzeResumeJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $temporaryFile,
        public string $userId
    ) {}

    public function handle(): void
    {
        broadcast(new AnalyzeResumeEvent(ResumeAnalyzeStatus::Processing, [], $this->userId));

        $temporaryFile = TemporaryUploadedFile::createFromLivewire($this->temporaryFile);

        /** @var CandidateOnboardingDTO $fields */
        $fields = resolve(AiAutocompleteInterface::class)->execute($temporaryFile);

        broadcast(new AnalyzeResumeEvent(ResumeAnalyzeStatus::Finished, $fields->jsonSerialize(), $this->userId));

    }
}
