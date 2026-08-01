<?php

declare(strict_types=1);

namespace App\Filament\Shared\Fields;

use App\Support\Validation\DeliverableEmail;
use Filament\Forms\Components\TextInput;

/**
 * Campo de e-mail para endereços que a aplicação precisa efetivamente entregar.
 *
 * O `->email()` do Filament aplica a regra `email` padrão, que aceita domínio sem
 * TLD — ver {@see DeliverableEmail} para o porquê de isso não bastar.
 */
final class EmailTextInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->email();
        $this->rule(DeliverableEmail::RULE);
    }
}
