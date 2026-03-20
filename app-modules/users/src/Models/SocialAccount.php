<?php

declare(strict_types=1);

namespace He4rt\Users\Models;

use App\Models\BaseModel;
use DutchCodingCompany\FilamentSocialite\Models\Contracts\FilamentSocialiteUser;
use He4rt\Users\Database\Factories\SocialAccountFactory;
use He4rt\Users\Enums\OAuthProvider;
use He4rt\Users\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;

/**
 * @property string $id
 * @property string $user_id
 * @property OAuthProvider $provider
 * @property string $provider_id
 * @property string|null $access_token
 * @property string|null $provider_username
 *
 * @extends BaseModel<SocialAccountFactory>
 */
#[UseFactory(SocialAccountFactory::class)]
class SocialAccount extends BaseModel implements FilamentSocialiteUser
{
    protected $table = 'social_accounts';

    protected $hidden = ['access_token'];

    public static function findForProvider(string $provider, SocialiteUserContract $oauthUser): ?static
    {
        return static::query()
            ->where('provider', $provider)
            ->where('provider_id', (string) $oauthUser->getId())
            ->first();
    }

    public static function createForProvider(string $provider, SocialiteUserContract $oauthUser, Authenticatable $user): static
    {
        return static::query()->create([
            'user_id' => $user->getKey(),
            'provider' => $provider,
            'provider_id' => (string) $oauthUser->getId(),
            'provider_username' => $oauthUser->getNickname() ?? $oauthUser->getName(),
        ]);
    }

    public function getUser(): Authenticatable
    {
        assert($this->user instanceof Authenticatable);

        return $this->user;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'provider' => OAuthProvider::class,
        ];
    }
}
