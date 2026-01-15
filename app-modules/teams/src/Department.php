<?php

declare(strict_types=1);

namespace He4rt\Teams;

use App\Models\BaseModel;
use Carbon\Carbon;
use He4rt\Teams\Database\Factories\DepartmentFactory;
use He4rt\Teams\Policies\DepartmentPolicy;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read string $id
 * @property string $name
 * @property string $description
 * @property string $head_user_id
 * @property string $team_id
 * @property-read Team $team
 * @property-read User $headUser
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
    use SoftDeletes;

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function headUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }
}
