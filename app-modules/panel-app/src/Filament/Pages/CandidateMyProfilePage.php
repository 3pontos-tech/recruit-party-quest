<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Pages;

use Jeffgreco13\FilamentBreezy\Pages\MyProfilePage;

class CandidateMyProfilePage extends MyProfilePage
{
    protected string $view = 'panel-app::filament.pages.my-profile-tabbed';

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
