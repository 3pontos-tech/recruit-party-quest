<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Support\Facades\Route;

// Um abraço! (Big hug)
// Claude TODO: remove this in two months from this commit.
Route::get('/job-requisitions/{record:id}', fn (JobRequisition $record) => redirect()->to('/vagas/'.$record->post->slug));
