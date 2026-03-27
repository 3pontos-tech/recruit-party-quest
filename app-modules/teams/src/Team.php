<?php

declare(strict_types=1);

namespace He4rt\Teams;

use App\Models\BaseModel;
use He4rt\Links\HasLinks;
use He4rt\Location\Concerns\HasAddresses;
use He4rt\Teams\Database\Factories\TeamFactory;
use He4rt\Teams\Policies\TeamPolicy;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property-read string $id
 * @property string $name
 * @property string $description
 * @property string $slug
 * @property string|null $owner_id
 * @property TeamStatus $status
 * @property string $contact_email
 * @property string|null $about
 * @property string|null $work_schedule
 * @property string|null $accessibility_accommodations
 * @property bool $is_disability_confident
 * @property-read User|null $owner
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
#[ObservedBy(TeamObserver::class)]
class Team extends BaseModel implements HasMedia
{
    use HasAddresses;
    use HasLinks;
    use InteractsWithMedia;
    use SoftDeletes;

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return BelongsToMany<User, $this, TeamMember>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'team_user',
            'team_id',
            'user_id'
        )->using(TeamMember::class)->withTimestamps();
    }

    /**
     * @return HasMany<Department, $this>
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsFile(fn (File $file): bool => in_array(
                $file->mimeType,
                ['image/jpeg', 'image/png', 'image/webp'],
                true
            ));
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections('logo')
            ->width(200)
            ->height(200);
    }

    protected function casts(): array
    {
        return [
            'status' => TeamStatus::class,
            'is_disability_confident' => 'boolean',
        ];
    }
}
