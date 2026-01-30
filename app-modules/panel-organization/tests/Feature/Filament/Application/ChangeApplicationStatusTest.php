<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Actions\RejectApplicationAction;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Models\Application;
use He4rt\Feedback\Enums\EvaluationRatingEnum;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Permissions\Permission;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    $this->evaluator = Recruiter::factory()->createOne();
    actingAs($this->evaluator->user);
    Permission::factory()
        ->create([
            'name' => 'view_applications',
            'guard_name' => 'web',
            'action' => PermissionsEnum::View,
        ]);
    Permission::factory()
        ->create([
            'name' => 'view_any_applications',
            'guard_name' => 'web',
            'action' => PermissionsEnum::View,
        ]);
    $this->evaluator->user->givePermissionTo('view_applications');
    $this->evaluator->user->givePermissionTo('view_any_applications');

    $this->application = Application::factory()->createOne();
    $requisition = $this->application->requisition;
    JobPosting::factory()->for($requisition)->create();
    filament()->setTenant($this->application->team);
});

it('should render', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => filament()->getTenant(),
        'record' => $this->application->getKey(),
    ])->assertOk();
});

it('should pass user to review stage', function (): void {

    $data = [
        'to_status' => ApplicationStatusEnum::InReview->value,
        'notes' => '123',
        'team_id' => filament()->getTenant()->getKey(),
        'application_id' => $this->application->getKey(),
        'evaluator_id' => $this->evaluator->getKey(),
        'overall_rating' => EvaluationRatingEnum::Maybe,
        'criteria_scores' => [
            'technical_skills' => '1',
            'communication' => '2',
            'problem_solving' => '3',
            'culture_fit' => '4',
        ],
        'comments' => 'comments',
        'recommendation' => 'reccom',
        'strengths' => 'stdhauda',
        'concerns' => 'fuedase',
    ];

    livewire(ViewApplication::class, [
        'tenant' => filament()->getTenant(),
        'record' => $this->application->getKey(),
    ])
        ->assertOk()
        ->mountAction('state-transition-action')
        ->assertActionVisible('state-transition-action')
        ->setActionData($data)
        ->callMountedAction()
        ->assertHasNoActionErrors();
})->skip();

it('should be able to reject an application', function (): void {
    $sut = new RejectApplicationAction();
    $sut->execute(
        applicationId: $this->application->getKey(),
        reason: RejectionReasonCategoryEnum::Availability,
        details: 'n sei'
    );
    assertDatabaseHas(Application::class, [
        'rejection_reason_details' => 'n sei',
        'id' => $this->application->getKey(),
        'rejection_reason_category' => RejectionReasonCategoryEnum::Availability,
    ]);
    // Just for now
});
