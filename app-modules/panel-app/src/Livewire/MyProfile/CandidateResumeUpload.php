<?php

declare(strict_types=1);

namespace He4rt\App\Livewire\MyProfile;

use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use He4rt\App\Filament\Pages\CandidateMyProfilePage;
use He4rt\App\Filament\Schemas\ProfileResumeFileUpload;
use He4rt\Candidates\Actions\Onboarding\StoreCandidateResume;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Users\User;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;
use Livewire\Attributes\On;

/**
 * @property mixed $form
 */
class CandidateResumeUpload extends MyProfileComponent
{
    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** @var int */
    public static $sort = 20;

    public ?User $user = null;

    public bool $isOnCooldown = false;

    public int $cooldownDaysRemaining = 0;

    protected string $view = 'panel-app::livewire.my-profile.candidate-resume-upload';

    public function mount(): void
    {
        $this->user = auth()->user();
        $candidate = $this->user?->candidate;

        if ($candidate !== null) {
            $this->isOnCooldown = $candidate->isCvUploadOnCooldown();
            $this->cooldownDaysRemaining = $candidate->cvCooldownDaysRemaining();
        }

        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ProfileResumeFileUpload::make('cv_file'),
            ])
            ->statePath('data');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    #[On('echo-private:candidate-onboarding.resume.{user.id},.finished')]
    public function finished(array $payload): void
    {
        $candidate = auth()->user()?->candidate;

        if ($candidate === null) {
            return;
        }

        resolve(StoreCandidateResume::class)->execute(
            $candidate,
            CandidateOnboardingDTO::make($payload['fields'] ?? []),
        );

        $candidate->update(['cv_last_uploaded_at' => now()]);

        $this->isOnCooldown = true;
        $this->cooldownDaysRemaining = 3;

        Notification::make()
            ->success()
            ->title(__('panel-app::pages/settings.resume_upload.notify_success'))
            ->send();

        $this->redirect(CandidateMyProfilePage::getUrl());
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    #[On('echo-private:candidate-onboarding.resume.{user.id},.error')]
    public function error(array $payload): void
    {
        Notification::make()
            ->danger()
            ->title($payload['message'] ?? __('panel-app::pages/settings.resume_upload.notify_error'))
            ->send();
    }
}
