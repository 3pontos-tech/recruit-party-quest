<?php

declare(strict_types=1);

namespace He4rt\Screening\Actions;

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Models\ScreeningQuestion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

/** Conta perguntas eliminatórias por vaga, somando as da própria vaga e as de suas etapas (exige `stages` carregadas). */
final class CountKnockoutQuestionsByRequisition
{
    /**
     * @param  Collection<int, JobRequisition>  $requisitions
     * @return array<string, int>
     */
    public function execute(Collection $requisitions): array
    {
        if ($requisitions->isEmpty()) {
            return [];
        }

        $stageToRequisition = [];

        foreach ($requisitions as $requisition) {
            foreach ($requisition->stages as $stage) {
                $stageToRequisition[(string) $stage->getKey()] = (string) $requisition->getKey();
            }
        }

        $requisitionAlias = Relation::getMorphAlias(JobRequisition::class);
        $stageAlias = Relation::getMorphAlias(Stage::class);
        $requisitionIds = $requisitions->map(fn (JobRequisition $requisition): string => (string) $requisition->getKey())->all();

        $questions = ScreeningQuestion::query()
            ->where('is_knockout', true)
            ->where(function (Builder $query) use ($requisitionAlias, $stageAlias, $requisitionIds, $stageToRequisition): void {
                $query
                    ->where(fn (Builder $query) => $query->where('screenable_type', $requisitionAlias)->whereIn('screenable_id', $requisitionIds))
                    ->orWhere(fn (Builder $query) => $query->where('screenable_type', $stageAlias)->whereIn('screenable_id', array_keys($stageToRequisition)));
            })
            ->get(['screenable_type', 'screenable_id']);

        $counts = [];

        foreach ($questions as $question) {
            $requisitionId = $question->screenable_type === $stageAlias
                ? ($stageToRequisition[(string) $question->screenable_id] ?? null)
                : (string) $question->screenable_id;

            if ($requisitionId !== null) {
                $counts[$requisitionId] = ($counts[$requisitionId] ?? 0) + 1;
            }
        }

        return $counts;
    }
}
