<?php

declare(strict_types=1);

namespace He4rt\Candidates\Models;

use App\Models\BaseModel;
use He4rt\Candidates\Database\Factories\EducationFactory;
use He4rt\Candidates\Policies\EducationPolicy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $candidate_id
 * @property string $institution
 * @property string $degree
 * @property string $field_of_study
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property bool $is_enrolled
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Candidate $candidate
 *
 * @extends BaseModel<EducationFactory>
 */
#[UseFactory(EducationFactory::class)]
#[UsePolicy(EducationPolicy::class)]
class Education extends BaseModel
{
    use SoftDeletes;

    protected $table = 'candidate_education';

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
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_enrolled' => 'boolean',
        ];
    }
}
