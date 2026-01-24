<?php

declare(strict_types=1);

namespace He4rt\Recruitment;

use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Filament\Schema\KanbanBoard;
use He4rt\Recruitment\Stages\Filament\Schema\KanbanColumn;
use He4rt\Recruitment\Stages\Models\Stage;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\Column;

class RecruitmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Column::class, KanbanColumn::class);
        $this->app->bind(Board::class, KanbanBoard::class);
    }

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
