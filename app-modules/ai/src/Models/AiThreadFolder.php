<?php

declare(strict_types=1);

namespace He4rt\Ai\Models;

use App\Models\BaseModel;
use He4rt\Ai\Enums\AiAssistantApplication;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property AiAssistantApplication $application
 * @property string $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, AiThread> $threads
 * @property-read User $user
 */
final class AiThreadFolder extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'application',
        'user_id',
    ];

    public static function defaults(): array
    {
        return config('assistant.default_chat_folders');
    }

    /**
     * @return HasMany<AiThread, $this>
     */
    public function threads(): HasMany
    {
        return $this->hasMany(AiThread::class, 'folder_id');
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
            'application' => AiAssistantApplication::class,
        ];
    }
}
