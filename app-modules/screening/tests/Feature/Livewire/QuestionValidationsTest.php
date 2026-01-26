<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Livewire\JobApplicationForm;
use He4rt\Screening\Models\ScreeningQuestion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->candidate = Candidate::factory()->create();
    $user = $this->candidate->user->refresh();
    actingAs($user);
    filament()->setCurrentPanel(FilamentPanel::App->value);
    $this->jobRequisition = JobRequisition::factory()->create();
    $this->stage = Stage::factory()->recycle($this->jobRequisition)->create();

    $this->question = ScreeningQuestion::factory()
        ->for($this->jobRequisition, 'screenable')
        ->state([
            'question_text' => 'fuedase?',
        ])
        ->fileUpload()
        ->required()
        ->create();

    Storage::fake('public');
    $this->file = UploadedFile::fake()->create('curriculum.pdf');

});

describe('file upload question', function (): void {
    test('required validation', function (): void {
        $questionId = $this->question->getKey();
        $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
            ->assertOk()
            ->call('submit');
        $livewire->assertHasErrors(['responses.'.$questionId => __('screening::question_validations.required')]);
    });
});

describe('multiple questions', function (): void {
    test('min', function (): void {
        $min = 1;
        $question = ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->multipleChoice($min)
            ->required()
            ->create();

        $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
            ->assertOk()
            ->call('submit');
        $livewire->assertHasErrors(['responses.'.$question->getKey() => __('screening::question_validations.min-options', ['min' => $min])]);

    });
    test('max', function (): void {
        $min = 1;
        $max = 1;
        $question = ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->multipleChoice($min, $max)
            ->required()
            ->create();

        $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
            ->assertOk()
            ->set('responses.'.$question->getKey(), [0 => 'oi', 1 => 'iai'])
            ->call('submit');
        $livewire->assertHasErrors(['responses.'.$question->getKey() => __('screening::question_validations.max-options', ['max' => $max])]);

    });
    test('null should be considere empty', function (): void {
        $min = 1;
        $question = ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->multipleChoice($min)
            ->required()
            ->create();

        $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
            ->assertOk()
            ->set('responses.'.$question->getKey(), [0 => null, 1 => null])
            ->call('submit');
        $livewire->assertHasErrors(['responses.'.$question->getKey() => __('screening::question_validations.min-options', ['min' => $min])]);

    });
});

describe('yes no type', function (): void {
    test('required', function (): void {
        $question = ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->yesNo()
            ->required()
            ->create();

        $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
            ->assertOk()
            ->call('submit');
        $livewire->assertHasErrors(['responses.'.$question->getKey() => __('screening::question_validations.required')]);
    });
});
describe('text type', function (): void {
    test('required', function (): void {
        $question = ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->text()
            ->required()
            ->create();

        $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
            ->assertOk()
            ->call('submit');
        $livewire->assertHasErrors(['responses.'.$question->getKey() => __('screening::question_validations.required')]);
    });
    test('max', function (): void {
        $question = ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->text(5)
            ->required()
            ->create();

        $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
            ->assertOk()
            ->set('responses.'.$question->getKey(), '1234567')
            ->call('submit');
        $livewire->assertHasErrors(['responses.'.$question->getKey() => __('screening::question_validations.max', ['max' => 5])]);
    });
});

describe('single choice', function (): void {
    test('required', function (): void {
        $question = ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->singleChoice()
            ->required()
            ->create();

        $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
            ->assertOk()
            ->call('submit');
        $livewire->assertHasErrors(['responses.'.$question->getKey() => __('screening::question_validations.required')]);
    });
});
describe('number choice', function (): void {
    test('required', function (): void {
        $question = ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->number()
            ->required()
            ->create();

        $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
            ->assertOk()
            ->call('submit');
        $livewire->assertHasErrors(['responses.'.$question->getKey() => __('screening::question_validations.required')]);
    });
    test('numeric', function (): void {
        $question = ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->number()
            ->required()
            ->create();

        $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
            ->assertOk()
            ->set('responses.'.$question->getKey(), 'nun')
            ->call('submit');
        $livewire->assertHasErrors(['responses.'.$question->getKey() => __('screening::question_validations.numeric')]);
    });
    test('min', function (): void {
        $question = ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->number(2)
            ->required()
            ->create();

        $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
            ->assertOk()
            ->set('responses.'.$question->getKey(), -1)
            ->call('submit');
        $livewire->assertHasErrors(['responses.'.$question->getKey() => __('screening::question_validations.numeric-min', ['min' => 2])]);
    });
    test('max', function (): void {
        $max = 2;
        $question = ScreeningQuestion::factory()
            ->for($this->jobRequisition, 'screenable')
            ->number(0, $max)
            ->required()
            ->create();

        $livewire = livewire(JobApplicationForm::class, ['requisition' => $this->jobRequisition])
            ->assertOk()
            ->set('responses.'.$question->getKey(), 4)
            ->call('submit');
        $livewire->assertHasErrors(['responses.'.$question->getKey() => __('screening::question_validations.numeric-max', ['max' => $max])]);
    });
});
