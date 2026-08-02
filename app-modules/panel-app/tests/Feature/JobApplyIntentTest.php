<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Candidates\Actions\EnsureCandidateProfile;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::App->value);

    $this->user = User::factory()->create();
    $this->candidate = resolve(EnsureCandidateProfile::class)->execute($this->user);
    $this->user->setRelation('candidate', $this->candidate);

    $team = Team::factory()->create();
    $department = Department::factory()->for($team)->create();
    $recruiter = Recruiter::factory()->for($team)->create();

    $this->jobRequisition = JobRequisition::factory()
        ->for($team)
        ->for($department)
        ->for($recruiter, 'recruiter')
        ->for($this->user, 'createdBy')
        ->create([
            'is_confidential' => false,
            'is_internal_only' => false,
            'status' => RequisitionStatusEnum::Published,
        ]);

    $this->posting = JobPosting::factory()
        ->for($this->jobRequisition, 'jobRequisition')
        ->create();
});

describe('Apply intent route', function (): void {
    it('redirects a guest to the login page and stores the intent as url.intended', function (): void {
        $intentUrl = route('filament.app.jobs.apply-intent', ['record' => $this->posting->slug]);

        get($intentUrl)
            ->assertRedirect(route('filament.app.auth.login'))
            ->assertSessionHas('url.intended', $intentUrl);
    });

    it('redirects an authenticated candidate to the job page with the apply flag', function (): void {
        $this->candidate->update(['is_onboarded' => true]);

        actingAs($this->user);

        get(route('filament.app.jobs.apply-intent', ['record' => $this->posting->slug]))
            ->assertRedirect(route('filament.app.resources.vagas.view', [
                'record' => $this->posting->slug,
                'apply' => 1,
            ]));
    });

    it('redirects to the jobs list with a notification when the posting no longer exists', function (): void {
        $this->candidate->update(['is_onboarded' => true]);

        actingAs($this->user);

        get(route('filament.app.jobs.apply-intent', ['record' => 'vaga-inexistente']))
            ->assertRedirect(route('filament.app.resources.vagas.index'))
            ->assertSessionHas('filament.notifications');
    });

    it('redirects to the jobs list with a notification when the requisition is no longer published', function (): void {
        $this->jobRequisition->update(['status' => RequisitionStatusEnum::Closed]);

        $this->candidate->update(['is_onboarded' => true]);

        actingAs($this->user);

        get(route('filament.app.jobs.apply-intent', ['record' => $this->posting->slug]))
            ->assertRedirect(route('filament.app.resources.vagas.index'))
            ->assertSessionHas('filament.notifications');
    });
});

describe('Job page apply intent UI', function (): void {
    it('renders the guest apply button pointing to the intent route', function (): void {
        get(route('filament.app.resources.vagas.view', ['record' => $this->posting->slug]))
            ->assertOk()
            ->assertSee(route('filament.app.jobs.apply-intent', ['record' => $this->posting->slug]));
    });

    it('auto-opens the application modal when returning with the apply flag', function (): void {
        ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->text()
            ->required()
            ->create();

        $this->candidate->update(['is_onboarded' => true]);

        actingAs($this->user);

        get(route('filament.app.resources.vagas.view', [
            'record' => $this->posting->slug,
            'apply' => 1,
        ]))
            ->assertOk()
            ->assertSee('showApplicationModal: true', escape: false);
    });

    it('keeps the application modal closed without the apply flag', function (): void {
        ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->text()
            ->required()
            ->create();

        $this->candidate->update(['is_onboarded' => true]);

        actingAs($this->user);

        get(route('filament.app.resources.vagas.view', ['record' => $this->posting->slug]))
            ->assertOk()
            ->assertSee('showApplicationModal: false', escape: false);
    });
});
