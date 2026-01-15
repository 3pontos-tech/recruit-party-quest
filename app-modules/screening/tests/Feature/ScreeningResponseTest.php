<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;
use Illuminate\Database\UniqueConstraintViolationException;

it('can create a screening response', function (): void {
    $response = ScreeningResponse::factory()->create();

    expect($response)->toBeInstanceOf(ScreeningResponse::class)
        ->and($response->id)->not->toBeNull()
        ->and($response->response_value)->toBeArray();
});

it('can create a yes/no response', function (): void {
    $response = ScreeningResponse::factory()->yesNoResponse(true)->create();

    expect($response->response_value)->toBe(['value' => 'yes']);
});

it('can create a text response', function (): void {
    $response = ScreeningResponse::factory()->textResponse('My answer')->create();

    expect($response->response_value)->toBe(['value' => 'My answer']);
});

it('can create a number response', function (): void {
    $response = ScreeningResponse::factory()->numberResponse(42)->create();

    expect($response->response_value)->toBe(['value' => 42]);
});

it('can create a single choice response', function (): void {
    $response = ScreeningResponse::factory()->singleChoiceResponse('option_b')->create();

    expect($response->response_value)->toBe(['value' => 'option_b']);
});

it('can create a multiple choice response', function (): void {
    $response = ScreeningResponse::factory()->multipleChoiceResponse(['option_a', 'option_c'])->create();

    expect($response->response_value)->toBe(['value' => ['option_a', 'option_c']]);
});

it('can mark a response as knockout failed', function (): void {
    $response = ScreeningResponse::factory()->knockoutFailed()->create();

    expect($response->is_knockout_fail)->toBeTrue();
});

it('belongs to an application', function (): void {
    $application = Application::factory()->create();
    $response = ScreeningResponse::factory()->create(['application_id' => $application->id]);

    expect($response->application)->toBeInstanceOf(Application::class)
        ->and($response->application->id)->toBe($application->id);
});

it('belongs to a question', function (): void {
    $question = ScreeningQuestion::factory()->create();
    $response = ScreeningResponse::factory()->create(['question_id' => $question->id]);

    expect($response->question)->toBeInstanceOf(ScreeningQuestion::class)
        ->and($response->question->id)->toBe($question->id);
});

it('can be accessed from application', function (): void {
    $application = Application::factory()->create();
    $question = ScreeningQuestion::factory()->create();

    ScreeningResponse::factory()->count(3)->create([
        'application_id' => $application->id,
        'question_id' => fn () => ScreeningQuestion::factory(),
    ]);

    expect($application->screeningResponses)->toHaveCount(3);
});

it('can be accessed from question', function (): void {
    $question = ScreeningQuestion::factory()->create();

    ScreeningResponse::factory()->count(2)->create([
        'question_id' => $question->id,
        'application_id' => fn () => Application::factory(),
    ]);

    expect($question->responses)->toHaveCount(2);
});

it('enforces unique application and question combination', function (): void {
    $application = Application::factory()->create();
    $question = ScreeningQuestion::factory()->create();

    ScreeningResponse::factory()->create([
        'application_id' => $application->id,
        'question_id' => $question->id,
    ]);

    ScreeningResponse::factory()->create([
        'application_id' => $application->id,
        'question_id' => $question->id,
    ]);
})->throws(UniqueConstraintViolationException::class);
