<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Models\JobRequisition;

function makeSalaryJob(bool $visible, ?int $min, ?int $max): JobRequisition
{
    $job = new JobRequisition();
    $job->show_salary_to_candidates = $visible;
    $job->salary_currency = 'BRL';
    $job->salary_range_min = $min;
    $job->salary_range_max = $max;

    return $job;
}

it('exposes the salary range only when visible to candidates with both bounds set', function (bool $visible, ?int $min, ?int $max, ?string $expected): void {
    expect(makeSalaryJob($visible, $min, $max)->salary_range_for_candidates)->toBe($expected);
})->with([
    'visible with both bounds' => [true, 8000, 12000, 'BRL 8.000 - 12.000'],
    'hidden from candidates' => [false, 8000, 12000, null],
    'missing minimum bound' => [true, null, 12000, null],
    'missing maximum bound' => [true, 8000, null, null],
]);
