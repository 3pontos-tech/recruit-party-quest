<?php

declare(strict_types=1);

namespace He4rt\Applications\Enums;

enum RejectionReasonCategoryEnum: string
{
    case Qualifications = 'qualifications';
    case Experience = 'experience';
    case CultureFit = 'culture_fit';
    case Compensation = 'compensation';
    case Location = 'location';
    case Availability = 'availability';
    case PositionFilled = 'position_filled';
    case Other = 'other';
}
