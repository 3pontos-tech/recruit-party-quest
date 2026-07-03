<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

it('is published only when status is Published', function (RequisitionStatusEnum $status, bool $expected): void {
    $job = new JobRequisition();
    $job->status = $status;

    expect($job->isPublished())->toBe($expected);
})->with([
    'draft' => [RequisitionStatusEnum::Draft, false],
    'pending approval' => [RequisitionStatusEnum::PendingApproval, false],
    'approved' => [RequisitionStatusEnum::Approved, false],
    'published' => [RequisitionStatusEnum::Published, true],
    'on hold' => [RequisitionStatusEnum::OnHold, false],
    'closed' => [RequisitionStatusEnum::Closed, false],
    'cancelled' => [RequisitionStatusEnum::Cancelled, false],
]);
