<?php

declare(strict_types=1);

namespace App\Filament\Shared\Pages;

use Filament\Auth\Pages\Login;

final class LoginPage extends Login
{
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
