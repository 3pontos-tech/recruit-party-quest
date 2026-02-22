<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Enums;

enum JobGenerationStatus: string
{
    case Processing = 'processing';

    case Success = 'success';

    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Processing => __('recruitment::enums.job_generation_status.processing'),
            self::Success => __('recruitment::enums.job_generation_status.success'),
            self::Error => __('recruitment::enums.job_generation_status.error'),
        };
    }
}
