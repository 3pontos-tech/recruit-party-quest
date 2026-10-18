<?php

declare(strict_types=1);

use He4rt\Feedback\Enums\EvaluationRatingEnum;

it('scores ratings from 1 (strong no) to 5 (strong yes)', function (): void {
    expect(EvaluationRatingEnum::StrongNo->score())->toBe(1)
        ->and(EvaluationRatingEnum::No->score())->toBe(2)
        ->and(EvaluationRatingEnum::Maybe->score())->toBe(3)
        ->and(EvaluationRatingEnum::Yes->score())->toBe(4)
        ->and(EvaluationRatingEnum::StrongYes->score())->toBe(5);
});
