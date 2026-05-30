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

it('formats the salary range when visible and both bounds are set', function (): void {
    expect(makeSalaryJob(true, 8000, 12000)->salary_range_for_candidates)
        ->toBe('BRL 8.000 - 12.000');
});

it('returns null when salary is hidden from candidates', function (): void {
    expect(makeSalaryJob(false, 8000, 12000)->salary_range_for_candidates)
        ->toBeNull();
});

it('returns null when a salary bound is missing', function (): void {
    expect(makeSalaryJob(true, null, 12000)->salary_range_for_candidates)->toBeNull();
    expect(makeSalaryJob(true, 8000, null)->salary_range_for_candidates)->toBeNull();
});
