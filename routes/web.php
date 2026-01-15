<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => to_route('filament.admin.auth.login'));
