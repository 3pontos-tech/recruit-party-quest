<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Observers;

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use Illuminate\Support\Str;

final class JobRequisitionObserver
{
    public function creating(JobRequisition $jobRequisition): void
    {
        $teamSlug = $jobRequisition->team->slug;
        $pattern = '/^(.+)-'.preg_quote($teamSlug, '/').'-[a-z0-9]{5}$/';

        if (preg_match($pattern, $jobRequisition->slug)) {
            return;
        }

        $titleSlug = Str::beforeLast($jobRequisition->slug, '-');
        $jobRequisition->slug = $titleSlug.'-'.$teamSlug.'-'.mb_strtolower((string) str()->random(5));
    }

    public function created(JobRequisition $jobRequisition): void
    {

        $stagesConfig = [
            ['type' => StageTypeEnum::New, 'duration' => 1],
            ['type' => StageTypeEnum::Screening, 'duration' => 3],
            ['type' => StageTypeEnum::Assessment, 'duration' => 7],
            ['type' => StageTypeEnum::Interview, 'duration' => 5],
            ['type' => StageTypeEnum::Offer, 'duration' => 5],
            ['type' => StageTypeEnum::Hired, 'duration' => 1],
        ];

        foreach ($stagesConfig as $index => $config) {
            $stageType = $config['type'];

            $jobRequisition->stages()->create([
                'team_id' => $jobRequisition->team_id,
                'stage_type' => $stageType,
                'name' => __('recruitment::default_stages.'.$stageType->value.'.name'),
                'description' => __('recruitment::default_stages.'.$stageType->value.'.description'),
                'expected_duration_days' => $config['duration'],
                'display_order' => $index + 1,
                'active' => true,
                'hidden' => false,
            ]);
        }
    }

    public function deleted(JobRequisition $jobRequisition): void
    {
        $jobRequisition->applications()->delete();
    }

    public function restored(JobRequisition $jobRequisition): void
    {
        $jobRequisition->applications()->withTrashed()->restore();
    }
}
