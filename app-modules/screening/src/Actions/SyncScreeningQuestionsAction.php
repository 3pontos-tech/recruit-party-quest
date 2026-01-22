<?php

declare(strict_types=1);

namespace He4rt\Screening\Actions;

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\DTOs\ScreeningQuestionDTO;
use He4rt\Screening\Models\ScreeningQuestion;
use Illuminate\Support\Facades\DB;

final class SyncScreeningQuestionsAction
{
    /**
     * Sync screening questions for a requisition.
     *
     * @param  array<int, ScreeningQuestionDTO>  $questions
     */
    public function handle(JobRequisition $requisition, array $questions): void
    {
        DB::transaction(function () use ($requisition, $questions): void {
            $this->deleteRemovedQuestions($requisition, $questions);
            $this->upsertQuestions($questions);
        });
    }

    /**
     * Delete questions that are no longer in the submitted data.
     *
     * @param  array<int, ScreeningQuestionDTO>  $questions
     */
    private function deleteRemovedQuestions(JobRequisition $requisition, array $questions): void
    {
        $submittedIds = array_filter(
            array_map(static fn (ScreeningQuestionDTO $dto): ?string => $dto->id, $questions)
        );

        $requisition->screeningQuestions()
            ->when($submittedIds !== [], fn ($query) => $query->whereNotIn('id', $submittedIds))
            ->when($submittedIds === [], fn ($query) => $query)
            ->delete();
    }

    /**
     * Create new questions and update existing ones.
     *
     * @param  array<int, ScreeningQuestionDTO>  $questions
     */
    private function upsertQuestions(array $questions): void
    {
        foreach ($questions as $dto) {
            if ($dto->isExisting()) {
                ScreeningQuestion::query()
                    ->where('id', $dto->id)
                    ->update($dto->toArray());
            } else {
                ScreeningQuestion::query()->create($dto->toArray());
            }
        }
    }
}
