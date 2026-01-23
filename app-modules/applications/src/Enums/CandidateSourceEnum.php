<?php

declare(strict_types=1);

namespace He4rt\Applications\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Contracts\HasLabel;

enum CandidateSourceEnum: string implements HasLabel
{
    use StringifyEnum;

    case LinkedIn = 'linkedin';
    case Indeed = 'indeed';
    case Glassdoor = 'glassdoor';
    case Referral = 'referral';
    case CareerPage = 'career_page';
    case Other = 'other';

    public function getLabel(): string
    {
        return __('applications::enums.candidate_source.'.$this->value.'.label');
    }
}
