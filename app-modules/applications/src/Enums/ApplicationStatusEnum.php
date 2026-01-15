<?php

declare(strict_types=1);

namespace He4rt\Applications\Enums;

enum ApplicationStatusEnum: string
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
}
