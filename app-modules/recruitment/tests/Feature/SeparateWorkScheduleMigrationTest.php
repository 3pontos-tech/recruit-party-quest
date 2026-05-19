<?php

declare(strict_types=1);

use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

function insertLegacyRequisition(string $legacyEmploymentType): string
{
    $team = Team::factory()->create();
    $dept = Department::factory()->create(['team_id' => $team->id]);
    $user = User::factory()->create();

    $id = (string) Str::uuid();

    DB::table('recruitment_job_requisitions')->insert([
        'id' => $id,
        'slug' => 'legacy-'.Str::lower(Str::random(8)),
        'team_id' => $team->id,
        'department_id' => $dept->id,
        'work_arrangement' => 'remote',
        'employment_type' => $legacyEmploymentType,
        'experience_level' => 'junior',
        'positions_available' => '1',
        'salary_currency' => 'BRL',
        'show_salary_to_candidates' => false,
        'created_by_id' => $user->id,
        'status' => 'draft',
        'priority' => 'medium',
        'is_internal_only' => false,
        'is_confidential' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $id;
}

it('has the nullable work_schedule column after migration', function (): void {
    expect(Schema::hasColumn('recruitment_job_requisitions', 'work_schedule'))->toBeTrue();
});

it('backfills legacy employment_type values into the two axes', function (string $legacy, ?string $expectedType, ?string $expectedSchedule): void {
    $id = insertLegacyRequisition($legacy);

    // Schema já migrado (coluna existe) → up() pula schema e roda apenas o backfill.
    $migration = require base_path('app-modules/recruitment/database/migrations/2026_05_18_000000_separate_work_schedule_from_employment_type.php');
    $migration->up();

    $row = DB::table('recruitment_job_requisitions')->where('id', $id)->first();

    expect($row->employment_type)->toBe($expectedType)
        ->and($row->work_schedule)->toBe($expectedSchedule);
})->with([
    'full_time_employee' => ['full_time_employee', null, 'full_time'],
    'part_time' => ['part_time', null, 'part_time'],
    'intern' => ['intern', 'intern', null],
    'clt' => ['clt', 'clt', null],
    'contractor' => ['contractor', 'contractor', null],
    'temporary' => ['temporary', 'temporary', null],
]);

it('reverses the schedule mapping on down()', function (): void {
    $id = insertLegacyRequisition('full_time_employee');

    $migration = require base_path('app-modules/recruitment/database/migrations/2026_05_18_000000_separate_work_schedule_from_employment_type.php');
    $migration->up();

    expect(DB::table('recruitment_job_requisitions')->where('id', $id)->value('work_schedule'))->toBe('full_time');

    $migration->down();

    $row = DB::table('recruitment_job_requisitions')->where('id', $id)->first();

    expect(Schema::hasColumn('recruitment_job_requisitions', 'work_schedule'))->toBeFalse()
        ->and($row->employment_type)->toBe('full_time_employee');
});
