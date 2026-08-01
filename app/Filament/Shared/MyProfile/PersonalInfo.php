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
    protected function getEmailComponent(): TextInput
    {
        return parent::getEmailComponent()
            ->rule(DeliverableEmail::RULE);
    }
}
