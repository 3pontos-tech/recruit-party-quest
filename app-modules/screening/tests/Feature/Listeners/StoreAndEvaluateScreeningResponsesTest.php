<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Screening\Collections\ScreeningResponseCollection;
use He4rt\Screening\DTOs\ScreeningResponseDTO;
use He4rt\Screening\Events\ScreeningEvaluated;
use He4rt\Screening\Events\ScreeningResponsesSubmitted;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseHas;

it('persists responses and evaluates when responses are submitted', function (): void {
    Event::fake([ScreeningEvaluated::class]);

    $application = Application::factory()->create();
    $question = ScreeningQuestion::factory()
        ->for($application->requisition, 'screenable')
        ->create();

    $responses = new ScreeningResponseCollection();
    $responses->add(new ScreeningResponseDTO(
        teamId: $application->team_id,
        applicationId: $application->getKey(),
        questionId: $question->getKey(),
        response_value: ['value' => 'yes'],
    ));

    event(new ScreeningResponsesSubmitted($application, $responses));

    assertDatabaseHas(ScreeningResponse::class, [
        'application_id' => $application->getKey(),
        'question_id' => $question->getKey(),
    ]);
    Event::assertDispatched(ScreeningEvaluated::class);
});
