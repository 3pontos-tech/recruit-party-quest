<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Models;

use App\Models\BaseModel;
use He4rt\Recruitment\Database\Factories\JobRequisitionItemFactory;
use He4rt\Recruitment\Requisitions\Enums\JobRequisitionItemTypeEnum;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Tags\HasTags;

/**
 * @property string $id
 * @property string $job_requisition_id
 * @property JobRequisitionItemTypeEnum $type
 * @property string $content
 * @property int $order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read JobRequisition $jobRequisition
 *
 * @extends BaseModel<JobRequisitionItemFactory>
 */
#[UseFactory(JobRequisitionItemFactory::class)]
class JobRequisitionItem extends BaseModel
{
    use HasTags;
    use SoftDeletes;

    protected $table = 'recruitment_job_requisition_items';

    /**
     * @return BelongsTo<JobRequisition, $this>
     */
    public function jobRequisition(): BelongsTo
    {
        return $this->belongsTo(JobRequisition::class);
    }

    protected function casts(): array
    {
        return [
            'type' => JobRequisitionItemTypeEnum::class,
            'order' => 'integer',
        ];
    }
}
