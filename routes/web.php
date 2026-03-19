<?php

declare(strict_types=1);

use App\Http\Controllers\GitHubCallbackController;
use App\Http\Controllers\GitHubConnectController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->get('/auth/github/callback', [GitHubCallbackController::class, 'handle'])
    ->name('github.callback');

Route::middleware(['web', 'auth'])->get('/github/connect', [GitHubConnectController::class, 'redirect'])
    ->name('github.connect');
