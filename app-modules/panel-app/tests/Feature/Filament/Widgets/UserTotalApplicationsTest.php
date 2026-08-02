<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Filament\Widgets\UserTotalApplications;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Actions\EnsureCandidateProfile;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();

    $this->candidate = resolve(EnsureCandidateProfile::class)->execute($this->user);
    $this->user->setRelation('candidate', $this->candidate);

    actingAs($this->user);
    filament()->setCurrentPanel(FilamentPanel::App->value);
});

describe('Rendering', function (): void {
    it('renders without errors', function (): void {
        livewire(UserTotalApplications::class)
            ->assertOk();
    });

    it('shows zero for all counts when the candidate has no applications', function (): void {
        livewire(UserTotalApplications::class)
            ->assertSeeInOrder([__('panel-app::filament.widgets.user_stats.total_applications'), '0'])
            ->assertSeeInOrder([__('panel-app::filament.widgets.user_stats.active_applications'), '0'])
            ->assertSeeInOrder([__('panel-app::filament.widgets.user_stats.offers_received'), '0']);
    });
});

describe('Total applications count', function (): void {
    it('counts all applications regardless of status', function (): void {
        Application::factory()->create([
            'candidate_id' => $this->candidate->getKey(),
            'status' => ApplicationStatusEnum::New,
        ]);
        Application::factory()->create([
            'candidate_id' => $this->candidate->getKey(),
            'status' => ApplicationStatusEnum::Rejected,
        ]);
        Application::factory()->create([
            'candidate_id' => $this->candidate->getKey(),
            'status' => ApplicationStatusEnum::Hired,
        ]);

        livewire(UserTotalApplications::class)
            ->assertSeeInOrder([__('panel-app::filament.widgets.user_stats.total_applications'), '3']);
    });
});

describe('Active applications count', function (): void {
    it('counts New, InReview, InProgress and OfferExtended as active', function (): void {
        foreach ([
            ApplicationStatusEnum::New,
            ApplicationStatusEnum::InReview,
            ApplicationStatusEnum::InProgress,
            ApplicationStatusEnum::OfferExtended,
        ] as $status) {
            Application::factory()->create([
                'candidate_id' => $this->candidate->getKey(),
                'status' => $status,
            ]);
        }

        livewire(UserTotalApplications::class)
            ->assertSeeInOrder([__('panel-app::filament.widgets.user_stats.active_applications'), '4']);
    });

    it('does not count Rejected, Hired and Withdrawn as active', function (): void {
        foreach ([
            ApplicationStatusEnum::Rejected,
            ApplicationStatusEnum::Hired,
            ApplicationStatusEnum::Withdrawn,
        ] as $status) {
            Application::factory()->create([
                'candidate_id' => $this->candidate->getKey(),
                'status' => $status,
            ]);
        }

        livewire(UserTotalApplications::class)
            ->assertSeeInOrder([__('panel-app::filament.widgets.user_stats.active_applications'), '0']);
    });
});

describe('Offers received count', function (): void {
    it('counts OfferExtended, OfferAccepted and OfferDeclined as offers', function (): void {
        foreach ([
            ApplicationStatusEnum::OfferExtended,
            ApplicationStatusEnum::OfferAccepted,
            ApplicationStatusEnum::OfferDeclined,
        ] as $status) {
            Application::factory()->create([
                'candidate_id' => $this->candidate->getKey(),
                'status' => $status,
            ]);
        }

        livewire(UserTotalApplications::class)
            ->assertSeeInOrder([__('panel-app::filament.widgets.user_stats.offers_received'), '3']);
    });

    it('counts OfferExtended in both active and offers at the same time', function (): void {
        Application::factory()->create([
            'candidate_id' => $this->candidate->getKey(),
            'status' => ApplicationStatusEnum::OfferExtended,
        ]);

        livewire(UserTotalApplications::class)
            ->assertSeeInOrder([__('panel-app::filament.widgets.user_stats.active_applications'), '1'])
            ->assertSeeInOrder([__('panel-app::filament.widgets.user_stats.offers_received'), '1']);
    });
});

describe('User isolation', function (): void {
    it('ignores applications from other users', function (): void {
        $otherUser = User::factory()->create();
        $otherCandidate = resolve(EnsureCandidateProfile::class)->execute($otherUser);

        Application::factory()->count(5)->create([
            'candidate_id' => $otherCandidate->getKey(),
            'status' => ApplicationStatusEnum::New,
        ]);

        livewire(UserTotalApplications::class)
            ->assertSeeInOrder([__('panel-app::filament.widgets.user_stats.total_applications'), '0']);
    });

    it('counts only the authenticated user applications when multiple users exist', function (): void {
        $otherUser = User::factory()->create();
        $otherCandidate = resolve(EnsureCandidateProfile::class)->execute($otherUser);

        Application::factory()->count(2)->create([
            'candidate_id' => $this->candidate->getKey(),
            'status' => ApplicationStatusEnum::InReview,
        ]);

        Application::factory()->count(9)->create([
            'candidate_id' => $otherCandidate->getKey(),
            'status' => ApplicationStatusEnum::InReview,
        ]);

        livewire(UserTotalApplications::class)
            ->assertSeeInOrder([__('panel-app::filament.widgets.user_stats.total_applications'), '2']);
    });
});
