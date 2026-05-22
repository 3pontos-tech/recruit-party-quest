<?php

declare(strict_types=1);

namespace He4rt\Feedback;

use He4rt\Feedback\Livewire\Comment as CommentLivewire;
use He4rt\Feedback\Models\Evaluation;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Kirschbaum\Commentions\CommentReaction;
use Livewire\Livewire;

class FeedbackServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'feedback');

        Relation::morphMap([
            'evaluations' => Evaluation::class,
        ]);

        CommentReaction::creating(function (CommentReaction $model): void {
            $model->id ??= (string) Str::uuid();
        });

        $this->app->booted(static function (): void {
            Livewire::component('commentions.comment', CommentLivewire::class);
        });
    }
}
