<?php

declare(strict_types=1);

namespace He4rt\Applications\Enums;

use Filament\Support\Contracts\HasLabel;

enum RejectionReasonCategoryEnum: string implements HasLabel
{
    case Qualifications = 'qualifications';
    case Experience = 'experience';
    case CultureFit = 'culture_fit';
    case Compensation = 'compensation';
    case Location = 'location';
    case Availability = 'availability';
    case PositionFilled = 'position_filled';
    case Other = 'other';

    public function getLabel(): string
    {
        return __('applications::enums.rejection_reason_category.'.$this->value.'.label');
    }
}
