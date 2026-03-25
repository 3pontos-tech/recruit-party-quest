<?php

declare(strict_types=1);

use He4rt\Candidates\AiAutocompleteInterface;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\Enums\ResumeAnalyzeStatus;
use He4rt\Candidates\Events\AnalyzeResumeEvent;
use He4rt\Candidates\Exceptions\OnboardingException;
use He4rt\Candidates\Jobs\AiAnalyzeResumeJob;
use He4rt\Users\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Event::fake([AnalyzeResumeEvent::class]);

    // Livewire uses 'tmp-for-tests' disk in testing environments
    Storage::fake('tmp-for-tests');
    Storage::disk('tmp-for-tests')->put('curriculum.pdf', 'fake pdf content');

    $this->temporaryFilename = 'curriculum.pdf';
});

it('broadcasts a Finished event when the CV analysis succeeds', function (): void {
    app()->bind(AiAutocompleteInterface::class, fn () => new class implements AiAutocompleteInterface
    {
        public function execute(TemporaryUploadedFile $file): CandidateOnboardingDTO
        {
            return CandidateOnboardingDTO::make(['education' => [], 'work_experiences' => []]);
        }
    });

    new AiAnalyzeResumeJob($this->temporaryFilename, $this->user->id)->handle();

    Event::assertDispatched(fn (AnalyzeResumeEvent $event) => $event->status === ResumeAnalyzeStatus::Finished
        && $event->userId === (string) $this->user->id);
});

it('broadcasts an Error event when the CV analysis throws OnboardingException', function (): void {
    $errorMessage = 'Something went wrong';
    $errorCode = 503;

    app()->bind(AiAutocompleteInterface::class, fn () => new readonly class($errorMessage, $errorCode) implements AiAutocompleteInterface
    {
        public function __construct(
            private string $message,
            private int $code,
        ) {}

        public function execute(TemporaryUploadedFile $file): CandidateOnboardingDTO
        {
            throw new OnboardingException($this->message, $this->code);
        }
    });

    new AiAnalyzeResumeJob($this->temporaryFilename, $this->user->id)->handle();

    Event::assertDispatched(fn (AnalyzeResumeEvent $event) => $event->status === ResumeAnalyzeStatus::Error
        && $event->userId === (string) $this->user->id
        && $event->message === $errorMessage
        && $event->code === $errorCode);
});
