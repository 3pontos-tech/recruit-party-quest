<?php

declare(strict_types=1);

namespace He4rt\Applications\Enums;

use Filament\Support\Contracts\HasLabel;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\AbstractApplicationTransition;
use He4rt\Applications\Services\Transitions\HiredTransition;
use He4rt\Applications\Services\Transitions\InProgressTransition;
use He4rt\Applications\Services\Transitions\InReviewTransition;
use He4rt\Applications\Services\Transitions\NewTransition;
use He4rt\Applications\Services\Transitions\OfferAcceptedTransition;
use He4rt\Applications\Services\Transitions\OfferDeclinedTransition;
use He4rt\Applications\Services\Transitions\OfferExtendedTransition;
use He4rt\Applications\Services\Transitions\RejectApplicationTransition;
use He4rt\Applications\Services\Transitions\WithdrawnTransition;

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

    public function getAction(Application $application): AbstractApplicationTransition
    {
        return match ($this) {
            self::New => new NewTransition($application),
            self::InReview => new InReviewTransition($application),
            self::InProgress => new InProgressTransition($application),
            self::OfferExtended => new OfferExtendedTransition($application),
            self::OfferAccepted => new OfferAcceptedTransition($application),
            self::OfferDeclined => new OfferDeclinedTransition($application),
            self::Hired => new HiredTransition($application),
            self::Rejected => new RejectApplicationTransition($application),
            self::Withdrawn => new WithdrawnTransition($application),
        };
    }
}
