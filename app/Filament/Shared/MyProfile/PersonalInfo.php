<?php

declare(strict_types=1);

namespace App\Filament\Shared\MyProfile;

use App\Support\Validation\DeliverableEmail;
use Filament\Forms\Components\TextInput;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo as BreezyPersonalInfo;

/**
 * Perfil do Breezy com a mesma exigência de e-mail entregável aplicada no cadastro.
 */
final class PersonalInfo extends BreezyPersonalInfo
{
    /**
     * O Breezy exige a senha atual para trocar o e-mail. Conta criada pelo login social
     * não tem senha e nunca passaria nessa confirmação, então o campo fica bloqueado e
     * a troca passa pelo suporte, que edita o e-mail no painel admin.
     */
    protected function getEmailComponent(): TextInput
    {
        return parent::getEmailComponent()
            ->rule(DeliverableEmail::RULE)
            ->disabled(fn (): bool => ! $this->hasPassword())
            ->helperText(fn (): ?string => $this->hasPassword()
                ? null
                : (string) __('panel-app::filament.profile.email_locked_without_password'));
    }

    private function hasPassword(): bool
    {
        return filled($this->user->getAuthPassword());
    }
}
