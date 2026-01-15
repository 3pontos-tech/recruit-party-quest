<?php

declare(strict_types=1);

namespace He4rt\Users;

use App\Enums\FilamentPanel;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use He4rt\Permissions\Permission;
use He4rt\Permissions\Role;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Stages\Concerns\InteractsWithInterviewStages;
use He4rt\Teams\Concerns\InteractsWithTenants;
use He4rt\Teams\Team;
use He4rt\Users\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read string $id
 * @property string $name
 * @property string $email
 * @property-read Carbon|null $email_verified_at
 * @property string $password
 * @property-read string|null $remember_token
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Collection|Team[] $teams
 * @property-read Collection|Role[] $roles
 * @property-read Collection|Permission[] $permissions
 */
#[UsePolicy(UserPolicy::class)]
#[UseFactory(UserFactory::class)]
final class User extends Authenticatable implements FilamentUser, HasAvatar, HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasRoles;
    use HasUuids;
    use InteractsWithInterviewStages;
    use InteractsWithMedia;
    use InteractsWithTenants;
    use LogsActivity;
    use Notifiable;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->currentPanel()) {
            FilamentPanel::Admin => $this->hasRole(Roles::SuperAdmin),
            default => false,
        };
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $avatar = $this->getFirstMedia('profile-pictures');

        return $avatar?->getUrl();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
