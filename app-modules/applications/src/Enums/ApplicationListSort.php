<?php

declare(strict_types=1);

namespace He4rt\Applications\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Contracts\HasLabel;

enum ApplicationListSort: string implements HasLabel
{
    use StringifyEnum;

    case Attention = 'attention';
    case DaysInStage = 'days_in_stage';
    case Applied = 'applied';
    case Name = 'name';
    case Stage = 'stage';

    public function getLabel(): string
    {
        return __('applications::enums.application_list_sort.'.$this->value.'.label');
    }
}
