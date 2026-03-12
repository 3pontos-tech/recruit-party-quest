<?php

declare(strict_types=1);

namespace He4rt\Candidates\Models;

use App\Models\BaseModel;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $candidate_id
 * @property string $job_requisition_id
 * @property Carbon $saved_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Candidate $candidate
 * @property-read JobRequisition $jobRequisition
 *
 * @extends BaseModel<Factory>
 */
class SavedJob extends BaseModel
{
    protected $table = 'candidate_saved_jobs';

    protected $fillable = [
        'candidate_id',
        'job_requisition_id',
        'saved_at',
    ];

    /**
     * @return BelongsTo<Candidate, $this>
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

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
            'saved_at' => 'datetime',
        ];
    }
}
