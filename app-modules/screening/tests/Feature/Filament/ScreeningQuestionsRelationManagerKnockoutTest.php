<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Actions\Testing\TestAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\EditJobRequisition;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\Filament\RelationManagers\ScreeningQuestionsRelationManager;
use He4rt\Screening\Models\ScreeningQuestion;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    $this->recruiter = Recruiter::factory()->createOne();
    actingAs($this->recruiter->user);
    $this->team = $this->recruiter->team;
    filament()->setTenant($this->team);

    $this->requisition = JobRequisition::factory()->create([
        'team_id' => $this->team->id,
    ]);
});

it('persists knockout_criteria.accepted for a single choice question (reproduction)', function (): void {
    livewire(ScreeningQuestionsRelationManager::class, [
        'ownerRecord' => $this->requisition,
        'pageClass' => EditJobRequisition::class,
    ])
        ->callAction(TestAction::make('create')->table(), data: [
            'question_text' => 'Qual sua senioridade?',
            'question_type' => QuestionTypeEnum::SingleChoice->value,
            'settings' => [
                'layout' => 'radio',
                'choices' => [
                    ['label' => 'aaa'],
                    ['label' => 'bbb'],
                ],
            ],
            'is_required' => true,
            'is_knockout' => true,
            'knockout_criteria' => [
                'accepted' => ['aaa'],
            ],
        ])
        ->assertHasNoActionErrors();

    $question = ScreeningQuestion::query()
        ->where('screenable_id', $this->requisition->id)
        ->firstOrFail();

    expect($question->is_knockout)->toBeTrue()
        ->and($question->knockout_criteria)->toBe(['accepted' => ['aaa']]);
});

it('keeps the selected accepted answer when filled step-by-step (interactive order)', function (): void {
    $component = livewire(ScreeningQuestionsRelationManager::class, [
        'ownerRecord' => $this->requisition,
        'pageClass' => EditJobRequisition::class,
    ])
        ->mountAction(TestAction::make('create')->table())
        ->setActionData([
            'question_text' => 'Qual sua senioridade?',
            'question_type' => QuestionTypeEnum::SingleChoice->value,
        ])
        ->setActionData([
            'settings' => [
                'layout' => 'radio',
                'choices' => [
                    ['label' => 'aaa'],
                    ['label' => 'bbb'],
                ],
            ],
        ])
        ->setActionData([
            'is_knockout' => true,
        ])
        ->setActionData([
            'knockout_criteria' => ['accepted' => ['aaa']],
        ]);

    // Decisive: after the interactive ordering, is the selection still in state?
    $component->assertActionDataSet(fn (array $data): bool => ($data['knockout_criteria']['accepted'] ?? []) === ['aaa']);

    $component->callMountedAction()->assertHasNoActionErrors();

    $question = ScreeningQuestion::query()
        ->where('screenable_id', $this->requisition->id)
        ->where('question_text', 'Qual sua senioridade?')
        ->firstOrFail();

    expect($question->knockout_criteria)->toBe(['accepted' => ['aaa']]);
});
