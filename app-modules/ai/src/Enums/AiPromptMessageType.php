<?php

declare(strict_types=1);

namespace He4rt\Ai\Enums;

enum AiPromptMessageType: string
{
    case User = 'user';
    case Assistant = 'assistant';
    case System = 'system';
    case Tool = 'tool';
}
