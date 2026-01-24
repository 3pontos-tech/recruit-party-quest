<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Staff\Recruiter;

use He4rt\Recruitment\Database\Factories\RecruiterFactory;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property string $user_id
 * @property string $team_id
 * @property bool $is_active
 * @property int $max_active_candidates
 * @property int $max_active_requisitions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property User $user
 * @property Team $team
 * @property Collection<int, JobRequisition> $requisition
 */
#[UseFactory(RecruiterFactory::class)]
class Recruiter extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return HasMany<JobRequisition, $this>
     */
    public function requisition(): HasMany
    {
        return $this->hasMany(JobRequisition::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'max_active_candidates' => 'integer',
            'max_active_requisitions' => 'integer',
        ];
    }
}
