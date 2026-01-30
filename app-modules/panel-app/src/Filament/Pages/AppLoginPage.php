<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Pages;

use Filament\Auth\Pages\Login;

final class AppLoginPage extends Login
{
    protected string $view = 'panel-app::filament.pages.app-login-page';

    protected static string $layout = 'panel-app::filament.layouts.simple';

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
