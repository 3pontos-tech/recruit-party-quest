<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Recruitment\Models;

use App\Models\BaseModel;
use He4rt\Recruitment\Database\Factories\JobPostingFactory;
use He4rt\Recruitment\Recruitment\Policies\JobPostingPolicy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * @property string $id
 * @property string $job_requisition_id
 * @property string $title
 * @property string $slug
 * @property string $summary
 * @property array $description
 * @property array $responsibilities
 * @property array $required_qualifications
 * @property array $preferred_qualifications
 * @property array $benefits
 * @property string $about_company
 * @property string $about_team
 * @property string $work_schedule
 * @property string $accessibility_accommodations
 * @property bool $is_disability_confident
 * @property string $external_post_url
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @extends BaseModel<JobPostingFactory>
 */

#[UseFactory(JobPostingFactory::class)]
#[UsePolicy(JobPostingPolicy::class)]
class JobPosting extends BaseModel
{
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
