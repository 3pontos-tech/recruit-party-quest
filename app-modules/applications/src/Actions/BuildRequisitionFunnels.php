<?php

declare(strict_types=1);

namespace He4rt\Applications\Actions;

use He4rt\Applications\Enums\ApplicationStatusGroup;
use He4rt\Applications\Models\Application;
use Illuminate\Support\Facades\DB;

/** Conta candidaturas abertas por etapa para um conjunto de vagas: [requisition_id => [stage_id => total]]. */
final class BuildRequisitionFunnels
{
    /**
     * @param  array<int, string>  $requisitionIds
     * @return array<string, array<string, int>>
     */
    public function execute(array $requisitionIds): array
    {
        if ($requisitionIds === []) {
            return [];
        }

        $rows = Application::query()
            ->whereIn('requisition_id', $requisitionIds)
            ->whereNotNull('current_stage_id')
            ->whereNotIn('status', ApplicationStatusGroup::Closed->values())
            ->select('requisition_id', 'current_stage_id', DB::raw('COUNT(*) AS total'))
            ->groupBy('requisition_id', 'current_stage_id')
            ->get();

        $funnels = [];

        foreach ($rows as $row) {
            $funnels[(string) $row->requisition_id][(string) $row->current_stage_id] = (int) $row->getAttribute('total');
        }

        return $funnels;
    }
}
