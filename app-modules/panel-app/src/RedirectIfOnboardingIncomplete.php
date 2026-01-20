<?php

declare(strict_types=1);

namespace He4rt\App;

use Closure;
use He4rt\Candidates\Models\Candidate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfOnboardingIncomplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        /** @var Candidate $candidate */
        $candidate = $request->user()->candidate;
        $isOnboarding = $request->route()->uri === 'onboarding';

        if (! $candidate->hasCompletedOnboarding() && ! $isOnboarding) {
            return redirect(route('filament.app.pages.onboarding'));
        }

        return $next($request);
    }
}
