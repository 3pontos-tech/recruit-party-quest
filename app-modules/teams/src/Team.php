<?php

declare(strict_types=1);

namespace He4rt\Teams;

use App\Models\BaseModel;
use He4rt\Teams\Database\Factories\TeamFactory;
use He4rt\Teams\Policies\TeamPolicy;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property-read string $id
 * @property string $name
 * @property string $description
 * @property string $slug
 * @property string $owner_id
 * @property TeamStatus $status
 * @property string $contact_email
 * @property-read User $owner
 * @property-read Collection|User[] $members
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon|null $deleted_at
 * @property-read Collection|Department[] $departments
 *
 * @extends BaseModel<TeamFactory>
 */
#[UsePolicy(TeamPolicy::class)]
#[UseFactory(TeamFactory::class)]
class Team extends BaseModel
{
    use SoftDeletes;

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'team_user',
            'team_id',
            'user_id'
        )->withTimestamps();
    }

    /**
     * @return HasMany<Department, $this>
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    protected function casts(): array
    {
        return [
            'status' => TeamStatus::class,
        ];
    }
}
