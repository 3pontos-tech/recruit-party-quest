<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Pages;

use Filament\Auth\Pages\Login;
use Filament\Support\Enums\Width;

final class AppLoginPage extends Login
{
    protected string $view = 'panel-app::filament.pages.app-login-page';

    protected static string $layout = 'panel-app::filament.layouts.auth-simple';

    protected Width|string|null $maxContentWidth = Width::ScreenTwoExtraLarge;

    public function mount(): void
    {
        parent::mount();

        if (! app()->isProduction()) {
            $this->form->fill([
                'email' => 'admin@admin.com',
                'password' => 'password',
                'remember' => true,
            ]);
        }
    }
}
