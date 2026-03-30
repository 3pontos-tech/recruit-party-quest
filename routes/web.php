<?php

declare(strict_types=1);

use App\Http\Controllers\GitHubCallbackController;
use App\Http\Controllers\GitHubConnectController;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->get('/auth/github/callback', [GitHubCallbackController::class, 'handle'])
    ->name('github.callback');

Route::middleware(['web', 'auth'])->get('/github/connect', [GitHubConnectController::class, 'redirect'])
    ->name('github.connect');

// Um abraço! (Big hug)
// Claude TODO: remove this in two months from this commit.
Route::get('/job-requisitions/{record:id}', fn (JobRequisition $record) => redirect()->to('/vagas/'.$record->post->slug));
