<?php

declare(strict_types=1);

namespace He4rt\Users\Models;

use App\Models\BaseModel;
use He4rt\Users\Database\Factories\UserGitHubConnectionFactory;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $user_id
 * @property string $github_id
 * @property string $github_username
 * @property string $access_token
 *
 * @extends BaseModel<UserGitHubConnectionFactory>
 */
#[UseFactory(UserGitHubConnectionFactory::class)]
class UserGitHubConnection extends BaseModel
{
    protected $table = 'user_github_connections';

    public static function findForUser(User $user): ?self
    {
        return self::query()->where('user_id', $user->id)->first();
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
        ];
    }
}
