<?php

declare(strict_types=1);

namespace App\Support\Validation;

/**
 * Política de e-mail da aplicação, em um único lugar.
 *
 * A regra `email` do Laravel usa `RFCValidation`, que aceita domínio sem TLD
 * (`fulano@hotmail`). Provedores de envio como o Resend recusam esses endereços:
 * o cadastro passa e a falha só aparece depois, na fila, no primeiro disparo.
 *
 * `strict` aplica `NoRFCWarningsValidation`, que exige TLD sem consultar DNS —
 * ao contrário de `dns`/`validateMxRecord()`, que fariam I/O de rede na validação
 * do formulário.
 */
final class DeliverableEmail
{
    public const string RULE = 'email:strict';
}
