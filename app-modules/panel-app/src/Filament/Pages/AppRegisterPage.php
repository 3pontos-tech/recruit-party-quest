<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Pages;

use App\Filament\Shared\Fields\EmailTextInput;
use Filament\Auth\Pages\Register;

final class AppRegisterPage extends Register
{
    /**
     * Idêntico ao campo padrão do Filament, trocando `TextInput` por
     * {@see EmailTextInput} para exigir um endereço entregável.
     */
    protected function getEmailFormComponent(): EmailTextInput
    {
        return EmailTextInput::make('email')
            ->label(__('filament-panels::auth/pages/register.form.email.label'))
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel());
    }
}
