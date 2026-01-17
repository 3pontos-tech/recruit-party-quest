<?php

declare(strict_types=1);

namespace He4rt\App;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfOnboardingIncomplete
{
    public function handle(Request $request, Closure $next): Response
    {

        return $next($request);
    }
}
