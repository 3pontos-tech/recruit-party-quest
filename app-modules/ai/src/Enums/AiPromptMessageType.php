<?php

declare(strict_types=1);

namespace He4rt\Ai\Enums;

use App\Enums\Concerns\StringifyEnum;

enum AiPromptMessageType: string
{
    use StringifyEnum;

    case User = 'user';
    case Assistant = 'assistant';
    case System = 'system';
    case Tool = 'tool';
}
