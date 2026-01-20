<?php

declare(strict_types=1);

namespace He4rt\Candidates;

use He4rt\Candidates\Actions\CompleteOnboardingAction;
use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\Education;
use He4rt\Candidates\Models\Skill;
use He4rt\Candidates\Models\WorkExperience;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class CandidatesServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'candidates');

        Relation::morphMap([
            'candidates' => Candidate::class,
            'candidate_educations' => Education::class,
            'candidate_skills' => Skill::class,
            'candidate_work_experiences' => WorkExperience::class,
        ]);
        $this->app->bind(AiAutocompleteInterface::class, CompleteOnboardingAction::class);
    }
}
