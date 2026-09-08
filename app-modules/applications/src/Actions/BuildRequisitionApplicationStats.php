<?php

declare(strict_types=1);

namespace He4rt\Applications\Actions;

use He4rt\Applications\DTOs\RequisitionApplicationStats;
use He4rt\Applications\Enums\ApplicationStatusGroup;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class BuildRequisitionApplicationStats
{
    public function execute(JobRequisition $requisition): RequisitionApplicationStats
    {
        $all = fn (): Builder => Application::query()->where('requisition_id', $requisition->getKey());
        $open = fn (): Builder => $all()->whereNotIn('status', ApplicationStatusGroup::Closed->values());
        $failed = fn (Builder $responses): Builder => $responses->where('is_knockout_fail', true);

        $byStage = $open()
            ->whereNotNull('current_stage_id')
            ->select('current_stage_id', DB::raw('COUNT(*) AS total'))
            ->groupBy('current_stage_id')
            ->pluck('total', 'current_stage_id')
            ->map(fn ($total): int => (int) $total)
            ->all();

        $overdue = $open()
            ->whereNotNull('current_stage_id')
            ->whereRaw(Application::STAGE_SINCE_SQL.' < NOW() - MAKE_INTERVAL(days => (SELECT s.expected_duration_days FROM recruitment_pipeline_stages s WHERE s.id = applications.current_stage_id AND s.expected_duration_days > 0))')
            ->count();

        return new RequisitionApplicationStats(
            total: $all()->count(),
            new: $all()->inStatusGroup(ApplicationStatusGroup::New)->count(),
            active: $all()->inStatusGroup(ApplicationStatusGroup::Active)->count(),
            closed: $all()->inStatusGroup(ApplicationStatusGroup::Closed)->count(),
            unseen: $open()->unseenByTeam()->count(),
            overdue: $overdue,
            knockoutPassed: $all()->whereHas('screeningResponses')->whereDoesntHave('screeningResponses', $failed)->count(),
            knockoutFailed: $all()->whereHas('screeningResponses', $failed)->count(),
            knockoutUnanswered: $all()->whereDoesntHave('screeningResponses')->count(),
            byStage: $byStage,
        );
    }
}
