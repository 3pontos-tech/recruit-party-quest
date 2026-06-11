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
 * @property Carbon|null $end_date
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

    /**
     * Total months between the start date and the effective end date.
     *
     * Returns null when the role is in the past but has no recorded end date,
     * so the duration is genuinely unknown and must not be assumed as "until today".
     */
    public function durationInMonths(): ?int
    {
        $end = $this->is_currently_working_here ? now() : $this->end_date;

        if ($end === null) {
            return null;
        }

        return (int) $this->start_date->diffInMonths($end);
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_currently_working_here' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
