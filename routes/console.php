<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('telescope:prune', ['--hours' => 36, '--keep-exceptions'])
    ->when(fn () => config('telescope.enabled'))
    ->daily();

Schedule::command('cloudflare:reload')
    ->when(fn () => config('laravelcloudflare.enabled'))
    ->daily();

Schedule::command('backup:run', ['--only-db'])
    ->when(fn () => config('backup.enabled'))
    ->everyOddHour();

Schedule::command('backup:clean')
    ->when(fn () => config('backup.enabled'))
    ->daily()
    ->at('03:00');

Schedule::command('backup:monitor')
    ->when(fn () => config('backup.enabled'))
    ->daily()
    ->at('04:00');
