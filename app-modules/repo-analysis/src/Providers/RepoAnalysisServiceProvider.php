<?php

declare(strict_types=1);

namespace He4rt\RepoAnalysis\Providers;

use He4rt\RepoAnalysis\Models\RepositoryAnalysis;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

final class RepoAnalysisServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'repo-analysis');

        Relation::morphMap([
            'repository_analysis' => RepositoryAnalysis::class,
        ]);
    }
}
