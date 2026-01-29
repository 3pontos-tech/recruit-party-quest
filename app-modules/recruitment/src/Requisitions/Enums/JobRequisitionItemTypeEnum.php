<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Enums;

use Filament\Support\Contracts\HasLabel;

enum JobRequisitionItemTypeEnum: string implements HasLabel
{
    case Responsibility = 'responsibility';
    case RequiredQualification = 'required_qualification';
    case PreferredQualification = 'preferred_qualification';
    case Benefit = 'benefit';

    public function getLabel(): string
    {
        return __('recruitment::requisitions.item_type.'.$this->value.'.label');
    }
}
