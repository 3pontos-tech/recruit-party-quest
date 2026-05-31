<?php

declare(strict_types=1);

use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Recruitment\Stages\Models\Stage;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The terminal pseudo-stages (`rejected`/`declined`) were created by the
     * JobRequisitionObserver for every requisition but never used by any
     * transition — rejection is a status overlay, not a stage move. This
     * migration removes the accumulated orphans.
     *
     * It is GUARDED: it counts every reference to the terminal stages first and
     * aborts without deleting anything if any reference exists. The deletion only
     * runs when all reference paths are empty, so production data is never lost.
     */
    public function up(): void
    {
        $terminalTypes = [
            StageTypeEnum::Rejected->value,
            StageTypeEnum::Declined->value,
        ];

        $terminalIds = DB::table('recruitment_pipeline_stages')
            ->whereIn('stage_type', $terminalTypes)
            ->pluck('id');

        if ($terminalIds->isEmpty()) {
            return;
        }

        $references = [
            'applications.current_stage_id' => DB::table('applications')
                ->whereIn('current_stage_id', $terminalIds)
                ->count(),
            'application_stage_history.from_stage_id' => DB::table('application_stage_history')
                ->whereIn('from_stage_id', $terminalIds)
                ->count(),
            'application_stage_history.to_stage_id' => DB::table('application_stage_history')
                ->whereIn('to_stage_id', $terminalIds)
                ->count(),
            'evaluations.stage_id' => DB::table('evaluations')
                ->whereIn('stage_id', $terminalIds)
                ->count(),
            'recruitment_stage_interviewer.pipeline_stage_id' => DB::table('recruitment_stage_interviewer')
                ->whereIn('pipeline_stage_id', $terminalIds)
                ->count(),
            'screening_questions.screenable_id' => DB::table('screening_questions')
                ->where('screenable_type', Relation::getMorphAlias(Stage::class))
                ->whereIn('screenable_id', $terminalIds)
                ->count(),
        ];

        $referenced = array_filter($references, fn (int $count): bool => $count > 0);

        if ($referenced !== []) {
            $details = collect($referenced)
                ->map(fn (int $count, string $path): string => sprintf('%s (%d)', $path, $count))
                ->implode(', ');

            throw new RuntimeException(
                sprintf('Aborting migration: found references to terminal pipeline stages, refusing to delete. Referenced paths: %s.', $details)
            );
        }

        DB::table('recruitment_pipeline_stages')
            ->whereIn('id', $terminalIds)
            ->delete();
    }
};
