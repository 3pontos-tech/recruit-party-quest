<?php

declare(strict_types=1);

namespace He4rt\Screening;

use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class ScreeningServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'screening');

        Relation::morphMap([
            'screening_questions' => ScreeningQuestion::class,
            'screening_responses' => ScreeningResponse::class,
        ]);
    }
}
