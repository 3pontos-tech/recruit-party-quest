<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationStageHistory;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Models\ScreeningQuestion;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function runRemoveTerminalStagesMigration(): void
{
    $path = base_path(
        'app-modules/recruitment/database/migrations/2026_05_30_000000_remove_terminal_pipeline_stages.php'
    );

    (require $path)->up();
}

/**
 * Cria um stage com um tipo terminal LEGADO ('rejected'/'declined') — tipos que
 * não existem mais no StageTypeEnum (removidos na #168). A factory monta o
 * registro; o tipo legado é gravado direto no banco, fora do cast do enum (que
 * rejeitaria o valor). Retorna o id do stage.
 */
function makeTerminalStage(string $type, ?JobRequisition $requisition = null): string
{
    $requisition ??= JobRequisition::factory()->create();

    $stage = Stage::factory()->create([
        'job_requisition_id' => $requisition->getKey(),
        'team_id' => $requisition->team_id,
    ]);

    DB::table('recruitment_pipeline_stages')
        ->where('id', $stage->getKey())
        ->update(['stage_type' => $type]);

    return $stage->getKey();
}

it('deletes orphan terminal stages when there are no references', function (): void {
    $requisition = JobRequisition::factory()->create();
    $nonTerminalCountBefore = $requisition->stages()->count();

    $rejected = makeTerminalStage('rejected', $requisition);
    $declined = makeTerminalStage('declined', $requisition);

    runRemoveTerminalStagesMigration();

    expect(DB::table('recruitment_pipeline_stages')->where('id', $rejected)->exists())->toBeFalse()
        ->and(DB::table('recruitment_pipeline_stages')->where('id', $declined)->exists())->toBeFalse()
        ->and(Stage::query()->count())->toBe($nonTerminalCountBefore);
});

it('is idempotent when there is nothing to delete', function (): void {
    $requisition = JobRequisition::factory()->create();
    $countBefore = Stage::query()->count();

    runRemoveTerminalStagesMigration();
    runRemoveTerminalStagesMigration();

    expect(Stage::query()->count())->toBe($countBefore)
        ->and($requisition->stages()->count())->toBe(6);
});

it('aborts without deleting anything when a terminal stage is referenced', function (string $path): void {
    $terminal = makeTerminalStage('rejected');

    match ($path) {
        'applications.current_stage_id' => Application::factory()->create([
            'current_stage_id' => $terminal,
        ]),
        'application_stage_history.from_stage_id' => ApplicationStageHistory::factory()->create([
            'from_stage_id' => $terminal,
        ]),
        'application_stage_history.to_stage_id' => ApplicationStageHistory::factory()->create([
            'to_stage_id' => $terminal,
        ]),
        'evaluations.stage_id' => Evaluation::factory()->create([
            'stage_id' => $terminal,
        ]),
        'recruitment_stage_interviewer.pipeline_stage_id' => DB::table('recruitment_stage_interviewer')->insert([
            'id' => Str::uuid()->toString(),
            'pipeline_stage_id' => $terminal,
            'recruiter_id' => Recruiter::factory()->create()->getKey(),
            'created_at' => now(),
            'updated_at' => now(),
        ]),
        'screening_questions.screenable_id' => ScreeningQuestion::factory()->create([
            'screenable_type' => Relation::getMorphAlias(Stage::class),
            'screenable_id' => $terminal,
        ]),
    };

    expect(fn () => runRemoveTerminalStagesMigration())->toThrow(RuntimeException::class);

    expect(DB::table('recruitment_pipeline_stages')->where('id', $terminal)->exists())->toBeTrue();
})->with([
    'applications.current_stage_id',
    'application_stage_history.from_stage_id',
    'application_stage_history.to_stage_id',
    'evaluations.stage_id',
    'recruitment_stage_interviewer.pipeline_stage_id',
    'screening_questions.screenable_id',
]);
