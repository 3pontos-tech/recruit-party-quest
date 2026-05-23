<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Actions;

use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Models\ScreeningQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class DuplicateJobRequisitionAction
{
    public function execute(JobRequisition $original, string $createdById): JobRequisition
    {
        return DB::transaction(function () use ($original, $createdById): JobRequisition {
            $original->loadMissing([
                'post',
                'items.tags',
                'screeningQuestions',
                'stages.screeningQuestions',
                'stages.interviewers',
            ]);

            $copySuffix = __('panel-organization::filament.actions.duplicate_job_requisition.title_suffix');

            $duplicate = $original->replicate([
                'slug',
                'status',
                'created_by_id',
                'approved_at',
                'published_at',
                'closed_at',
                'target_start_at',
            ]);

            $duplicate->status = RequisitionStatusEnum::Draft;
            $duplicate->created_by_id = $createdById;
            $duplicate->slug = Str::slug($original->post->title.' '.$copySuffix);
            $duplicate->save();

            // The JobRequisitionObserver creates the default pipeline stages on
            // `created`. Drop them so we can mirror the original stages instead.
            $duplicate->stages()->forceDelete();

            $post = $original->post->replicate(['job_requisition_id', 'slug']);
            $post->job_requisition_id = $duplicate->getKey();
            $post->slug = $duplicate->slug;
            $post->title = $original->post->title.' '.$copySuffix;
            $post->save();

            foreach ($original->items as $item) {
                $clonedItem = $item->replicate(['job_requisition_id']);
                $clonedItem->job_requisition_id = $duplicate->getKey();
                $clonedItem->save();

                if ($item->tags->isNotEmpty()) {
                    $clonedItem->syncTags($item->tags);
                }
            }

            foreach ($original->screeningQuestions as $question) {
                $duplicate->screeningQuestions()->save(
                    $question->replicate(['screenable_id', 'screenable_type'])
                );
            }

            $original->stages->each(function (Stage $stage) use ($duplicate): void {
                $clonedStage = $stage->replicate(['job_requisition_id']);
                $duplicate->stages()->save($clonedStage);

                $stage->screeningQuestions->each(function (ScreeningQuestion $question) use ($clonedStage): void {
                    $clonedStage->screeningQuestions()->save(
                        $question->replicate(['screenable_id', 'screenable_type'])
                    );
                });

                $interviewerIds = $stage->interviewers->pluck('id')->all();

                if ($interviewerIds !== []) {
                    $clonedStage->interviewers()->attach($interviewerIds);
                }
            });

            return $duplicate->refresh();
        });
    }
}
