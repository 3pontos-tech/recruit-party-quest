<?php

declare(strict_types=1);

namespace He4rt\Applications\Enums;

use Filament\Support\Contracts\HasLabel;

enum ApplicationStatusEnum: string implements HasLabel
{
    case New = 'new';
    case InReview = 'in_review';
    case InProgress = 'in_progress';
    case OfferExtended = 'offer_extended';
    case OfferAccepted = 'offer_accepted';
    case OfferDeclined = 'offer_declined';
    case Hired = 'hired';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';

    public function getLabel(): string
    {
        return __('applications::enums.application_status.'.$this->value.'.label');
    }
}
