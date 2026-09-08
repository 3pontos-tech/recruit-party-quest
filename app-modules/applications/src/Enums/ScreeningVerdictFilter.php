<?php

declare(strict_types=1);

namespace He4rt\Applications\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Contracts\HasLabel;

enum ScreeningVerdictFilter: string implements HasLabel
{
    use StringifyEnum;

    case All = 'all';
    case Passed = 'passed';
    case Failed = 'failed';
    case Unanswered = 'unanswered';

    public function getLabel(): string
    {
        return __('applications::enums.screening_verdict_filter.'.$this->value.'.label');
    }
}
