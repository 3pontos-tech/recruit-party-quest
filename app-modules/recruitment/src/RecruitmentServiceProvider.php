<?php

declare(strict_types=1);

namespace He4rt\Recruitment;

use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class RecruitmentServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'recruitment');

        Relation::morphMap([
            'job_postings' => JobPosting::class,
            'job_requisitions' => JobRequisition::class,
            'pipeline_stages' => Stage::class,
        ]);
    }
}
