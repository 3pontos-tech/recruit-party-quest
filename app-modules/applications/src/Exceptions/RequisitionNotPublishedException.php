<?php

declare(strict_types=1);

namespace He4rt\Applications\Exceptions;

use Exception;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

final class RequisitionNotPublishedException extends Exception
{
    public static function cannotApplyToRequisition(JobRequisition $requisition): self
    {
        return new self(sprintf(
            'Cannot apply to requisition %s: status is %s, not Published.',
            $requisition->getKey(),
            $requisition->status->value,
        ), 422);
    }
}
