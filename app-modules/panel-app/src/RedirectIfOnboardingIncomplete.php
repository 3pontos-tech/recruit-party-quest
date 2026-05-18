<?php

declare(strict_types=1);

namespace He4rt\App;

use Closure;
use He4rt\App\Filament\Pages\OnboardingWizard;
use He4rt\Candidates\Models\Candidate;
use He4rt\Permissions\Roles;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfOnboardingIncomplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        if ($request->routeIs('filament.app.auth.logout') || $request->routeIs('filament.app.auth.*')) {
            return $next($request);
        }

        if ($request->user()->hasAnyRole([Roles::SuperAdmin, Roles::Admin])) {
            return $next($request);
        }

        /** @var ?Candidate $candidate */
        $candidate = $request->user()->candidate;
        $isLandingPage = $request->routeIs('filament.app.pages.landing-page');
        $isOnboarding = $request->route()->uri === 'onboarding';

        if ((! $candidate || ! $candidate->hasCompletedOnboarding()) && ! $isOnboarding && ! $isLandingPage) {
            return redirect(OnboardingWizard::getUrl());
        }

        return $next($request);
    }
}
