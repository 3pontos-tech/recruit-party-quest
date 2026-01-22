<?php

declare(strict_types=1);

namespace He4rt\Ai\Enums;

use Filament\Support\Contracts\HasLabel;

enum AiPromptTabs: string implements HasLabel
{
    case Newest = 'newest';

    case MostLoved = 'most_loved';

    case MostViewed = 'most_viewed';

    public function getLabel(): string
    {
        return __('ai::filament.enums.prompt_tabs.'.$this->value.'.label');
    }
}
