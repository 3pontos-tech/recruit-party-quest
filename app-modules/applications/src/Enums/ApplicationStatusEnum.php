<?php

declare(strict_types=1);

namespace He4rt\Applications\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
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

enum ApplicationStatusEnum: string implements HasColor, HasIcon, HasLabel
{
    use StringifyEnum;

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

    public function getColor(): array
    {
        return match ($this) {
            self::New => Color::Gray,
            self::InReview => Color::Yellow,
            self::InProgress => Color::Blue,
            self::OfferExtended => Color::Indigo,
            self::OfferAccepted => Color::Green,
            self::OfferDeclined => Color::Red,
            self::Hired => Color::Emerald,
            self::Rejected => Color::Slate,
            self::Withdrawn => Color::Orange,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::New => Heroicon::Plus,
            self::InReview => Heroicon::Eye,
            self::InProgress => Heroicon::Clock,
            self::OfferExtended => Heroicon::Envelope,
            self::OfferAccepted => Heroicon::Check,
            self::OfferDeclined => Heroicon::XMark,
            self::Hired => Heroicon::User,
            self::Rejected => Heroicon::XCircle,
            self::Withdrawn => Heroicon::ArrowLeft,
        };
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
