<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Models;

use App\Models\BaseModel;
use He4rt\Recruitment\Database\Factories\JobPostingFactory;
use He4rt\Recruitment\Requisitions\Policies\JobPostingPolicy;
use He4rt\Teams\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property string $job_requisition_id
 * @property string $title
 * @property string $slug
 * @property string $summary
 * @property Collection<int,string> $description
 * @property Collection<int,string> $responsibilities
 * @property Collection<int,string> $required_qualifications
 * @property Collection<int,string> $preferred_qualifications
 * @property Collection<int,string> $benefits
 * @property string $about_company
 * @property string $about_team
 * @property string $work_schedule
 * @property string $accessibility_accommodations
 * @property bool $is_disability_confident
 * @property string $external_post_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * @extends BaseModel<JobPostingFactory>
 */
#[UseFactory(JobPostingFactory::class)]
#[UsePolicy(JobPostingPolicy::class)]
class JobPosting extends BaseModel
{
    use BelongsToTeam;
    use SoftDeletes;

    protected $table = 'recruitment_job_postings';

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
            'description' => 'array',
            'responsibilities' => 'array',
            'required_qualifications' => 'array',
            'preferred_qualifications' => 'array',
            'benefits' => 'array',
            'is_disability_confident' => 'boolean',
        ];
    }
}
