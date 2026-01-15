<?php

declare(strict_types=1);

namespace He4rt\Applications\Enums;

enum CandidateSourceEnum: string
{
    case LinkedIn = 'linkedin';
    case Indeed = 'indeed';
    case Glassdoor = 'glassdoor';
    case Referral = 'referral';
    case CareerPage = 'career_page';
    case Other = 'other';
}
