<?php

declare(strict_types=1);

namespace He4rt\Candidates\Models;

use App\Models\BaseModel;
use He4rt\Candidates\Database\Factories\WorkExperienceFactory;
use He4rt\Candidates\Policies\WorkExperiencePolicy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property string $candidate_id
 * @property string $company_name
 * @property string $description
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property bool $is_currently_working_here
 * @property Collection<int, string> $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Candidate $candidate
 *
 * @extends BaseModel<WorkExperienceFactory>
 */
#[UsePolicy(WorkExperiencePolicy::class)]
#[UseFactory(WorkExperienceFactory::class)]
class WorkExperience extends BaseModel
{
    use SoftDeletes;

    protected $table = 'candidate_work_experiences';

    /**
     * @return BelongsTo<Candidate, $this>
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'timestamp',
            'end_date' => 'timestamp',
            'is_currently_working_here' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
