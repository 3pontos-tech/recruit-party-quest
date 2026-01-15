<?php

declare(strict_types=1);

namespace He4rt\Recruitment;

use Illuminate\Support\ServiceProvider;

class RecruitmentServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'recruitment');
    }
}
