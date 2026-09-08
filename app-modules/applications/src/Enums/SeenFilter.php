<?php

declare(strict_types=1);

namespace He4rt\Applications\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Contracts\HasLabel;

enum SeenFilter: string implements HasLabel
{
    use StringifyEnum;

    case All = 'all';
    case Unseen = 'unseen';
    case Seen = 'seen';

    public function getLabel(): string
    {
        return __('applications::enums.seen_filter.'.$this->value.'.label');
    }
}
