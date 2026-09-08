<?php

declare(strict_types=1);

namespace He4rt\Applications\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Contracts\HasLabel;

enum ApplicationStatusGroup: string implements HasLabel
{
    use StringifyEnum;

    case New = 'new';
    case Active = 'active';
    case Offer = 'offer';
    case Closed = 'closed';

    public static function fromStatus(ApplicationStatusEnum $status): self
    {
        return match ($status) {
            ApplicationStatusEnum::New, ApplicationStatusEnum::InReview => self::New,
            ApplicationStatusEnum::InProgress => self::Active,
            ApplicationStatusEnum::OfferExtended, ApplicationStatusEnum::OfferAccepted, ApplicationStatusEnum::Hired => self::Offer,
            ApplicationStatusEnum::Rejected, ApplicationStatusEnum::Withdrawn, ApplicationStatusEnum::OfferDeclined => self::Closed,
        };
    }

    /**
     * @return array<int, ApplicationStatusEnum>
     */
    public function statuses(): array
    {
        return array_values(array_filter(
            ApplicationStatusEnum::cases(),
            fn (ApplicationStatusEnum $status): bool => self::fromStatus($status) === $this,
        ));
    }

    /**
     * @return array<int, string>
     */
    public function values(): array
    {
        return array_map(fn (ApplicationStatusEnum $status): string => $status->value, $this->statuses());
    }

    public function getLabel(): string
    {
        return __('applications::enums.application_status_group.'.$this->value.'.label');
    }
}
