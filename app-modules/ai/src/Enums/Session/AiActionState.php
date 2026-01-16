<?php

declare(strict_types=1);

namespace He4rt\Ai\Enums\Session;

use He4rt\Ai\Contracts\HasGuideline;

enum AiActionState: string implements HasGuideline
{
    case Starting = 'starting';
    case Continue = 'continue';
    case SoftReengage = 'soft_reengage';
    case LightenTone = 'lighten_tone';
    case WarnUser = 'warn_user';
    case TerminateConversation = 'terminate_conversation';

    public function getGuideline(): string
    {
        return match ($this) {
            self::Starting => 'Sem informações concretas, porém otimista sobre a conversa!',
            self::Continue => 'Continuando a conversa, não vendo problemas até o momento',
            self::SoftReengage => 'Vejo que o usuário está perdendo o interesse, mas podemos recuperar a conversa!',
            self::LightenTone => 'Vejo a perda de foco na conversa, talvez uma aproximação mais leve do meu lado.',
            self::WarnUser => 'O usuário está com um possível comportamento duvidoso, caso ele não mantenha uma conversa decente, podemos encerrar a conversa.',
            self::TerminateConversation => 'Well :D'
        };
    }
}
