<?php

declare(strict_types=1);

namespace He4rt\Teams;

use App\Models\BaseModel;
use Carbon\Carbon;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Teams\Concerns\BelongsToTeam;
use He4rt\Teams\Database\Factories\DepartmentFactory;
use He4rt\Teams\Policies\DepartmentPolicy;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read string $id
 * @property string $name
 * @property string $description
 * @property string $head_user_id
 * @property string $team_id
 * @property-read Team $team
 * @property-read User $headUser
 * @property-read Collection<int, JobRequisition> $requisitions
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon|null $deleted_at
 *
 * @extends BaseModel<DepartmentFactory>
 */
#[UsePolicy(DepartmentPolicy::class)]
#[UseFactory(DepartmentFactory::class)]
class Department extends BaseModel
{
    use BelongsToTeam;
    use SoftDeletes;

    /**
     * @return BelongsTo<User, $this>
     */
    public function headUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    /**
     * @return HasMany<JobRequisition, $this>
     */
    public function requisitions(): HasMany
    {
        return $this->hasMany(JobRequisition::class);
    }
}
