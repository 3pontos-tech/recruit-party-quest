<?php

declare(strict_types=1);

namespace He4rt\Screening\Actions\ScreeningResponse;

use He4rt\Applications\Models\Application;
use He4rt\Screening\Events\ScreeningEvaluated;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;
use He4rt\Screening\QuestionTypes\QuestionTypeRegistry;
use Illuminate\Support\Facades\Log;

final class EvaluateScreeningResponses
{
    public function execute(Application $application): void
    {
        $application->loadMissing(['requisition.screeningQuestions', 'screeningResponses']);

        $questions = $application->requisition?->screeningQuestions ?? collect(); // @phpstan-ignore nullsafe.neverNull

        $responsesByQuestion = $application->screeningResponses->keyBy('question_id');

        $hadKnockoutCriteria = false;
        $anyKnockoutFailed = false;

        foreach ($questions as $question) {
            /** @var ScreeningQuestion $question */
            if (! $question->is_knockout) {
                continue;
            }

            $criteria = $question->knockout_criteria ?? [];

            if ($criteria === []) {
                continue;
            }

            $hadKnockoutCriteria = true;

            /** @var ScreeningResponse|null $response */
            $response = $responsesByQuestion->get($question->getKey());

            if ($response === null) {
                continue;
            }

            $answer = $response->response_value['value'] ?? null;

            if (blank($answer)) {
                continue;
            }

            // EC-2 guard: if the recruiter renamed/deleted choices after
            // configuring the criteria, accepted values may reference options
            // that no longer exist. Stale references must never wrongly reject
            // a candidate (point-in-time, Decision 11). Drop stale values; if
            // none remain, the criterion is meaningless — skip and log.
            if (array_key_exists('accepted', $criteria)) {
                $sanitized = $this->withValidAccepted($criteria, $question);

                if ($sanitized === null) {
                    Log::warning('Screening knockout criteria is stale; accepted options no longer exist on the question', [
                        'question_id' => $question->getKey(),
                        'application_id' => $application->getKey(),
                    ]);

                    continue;
                }

                $criteria = $sanitized;
            }

            $typeClass = QuestionTypeRegistry::get($question->question_type);

            $passed = $typeClass::evaluateKnockout($criteria, $answer);

            if (! $passed) {
                $anyKnockoutFailed = true;
                $response->update(['is_knockout_fail' => true]);
            }
        }

        event(new ScreeningEvaluated(
            $application,
            anyKnockoutFailed: $anyKnockoutFailed,
            hadKnockoutCriteria: $hadKnockoutCriteria,
        ));
    }

    /**
     * Keep only accepted values that still exist among the question's current
     * choices. Returns null when none remain (fully stale criterion).
     *
     * @param  array<string, mixed>  $criteria
     * @return array<string, mixed>|null
     */
    private function withValidAccepted(array $criteria, ScreeningQuestion $question): ?array
    {
        $accepted = is_array($criteria['accepted'] ?? null) ? $criteria['accepted'] : [];

        $settings = $question->settings ?? [];
        $choices = is_array($settings['choices'] ?? null) ? $settings['choices'] : [];

        $currentValues = [];
        foreach ($choices as $choice) {
            if (! is_array($choice)) {
                continue;
            }

            $value = (string) ($choice['value'] ?? '');

            if ($value !== '') {
                $currentValues[] = $value;
            }
        }

        $valid = array_values(array_filter(
            array_map(strval(...), $accepted),
            static fn (string $value): bool => in_array($value, $currentValues, true),
        ));

        if ($valid === []) {
            return null;
        }

        $criteria['accepted'] = $valid;

        return $criteria;
    }
}
