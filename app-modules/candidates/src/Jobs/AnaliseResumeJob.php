<?php

declare(strict_types=1);

namespace He4rt\Candidates\Jobs;

use He4rt\Candidates\AiAutocompleteInterface;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\Events\ResumeAnalyzedEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AnaliseResumeJob implements ShouldQueue
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
        $temporaryFile = TemporaryUploadedFile::createFromLivewire($this->temporaryFile);

        /** @var CandidateOnboardingDTO $fields */
        $fields = resolve(AiAutocompleteInterface::class)->execute($temporaryFile);

        broadcast(new ResumeAnalyzedEvent($fields->jsonSerialize(), $this->userId));

    }
}
