<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Contracts\HasLabel;

enum RequisitionOverviewSort: string implements HasLabel
{
    use StringifyEnum;

    case New = 'new';
    case Unseen = 'unseen';
    case KnockoutPassed = 'knockout_passed';
    case Oldest = 'oldest';
    case Total = 'total';
    case Title = 'title';

    public function getLabel(): string
    {
        return __('recruitment::enums.requisition_overview_sort.'.$this->value.'.label');
    }
}
