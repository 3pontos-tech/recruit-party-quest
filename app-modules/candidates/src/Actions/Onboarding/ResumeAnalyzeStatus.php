<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

enum ResumeAnalyzeStatus: string
{
    case Queued = 'queued';

    case Processing = 'processing';

    case Finished = 'finished';

    case Error = 'error';
}
