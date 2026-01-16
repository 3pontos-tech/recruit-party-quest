<?php

declare(strict_types=1);

namespace He4rt\Feedback;

use He4rt\Feedback\Models\ApplicationComment;
use He4rt\Feedback\Models\Evaluation;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class FeedbackServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'feedback');

        Relation::morphMap([
            'application_comments' => ApplicationComment::class,
            'evaluations' => Evaluation::class,
        ]);
    }
}
