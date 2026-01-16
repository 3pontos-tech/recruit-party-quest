<?php

declare(strict_types=1);

namespace He4rt\Ai\Models;

use App\Models\BaseModel;
use He4rt\Ai\Events\AiMessageCreated;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string|null $prompt_id
 * @property string|null $message_id
 * @property string $content
 * @property string|null $context
 * @property array<mixed> $request
 * @property string $thread_id
 * @property string|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read AiThread $thread
 * @property-read User|null $user
 * @property-read Prompt|null $prompt
 */
final class AiMessage extends BaseModel
{
    use AsPivot;
    use Prunable;
    use SoftDeletes;

    protected $fillable = [
        'message_id',
        'content',
        'context',
        'request',
        'thread_id',
        'user_id',
        'prompt_id',
    ];

    protected $table = 'ai_messages';

    protected $dispatchesEvents = [
        'created' => AiMessageCreated::class,
    ];

    /**
     * @return BelongsTo<AiThread, $this>
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(AiThread::class, 'thread_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Prompt, $this>
     */
    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class)->withTrashed();
    }

    public function prunable(): Builder
    {
        return self::query()
            ->whereNotNull('deleted_at')
            ->where('deleted_at', '<=', now()->subDays(7))
            ->whereDoesntHave('files', fn (Builder $query) => $query->withTrashed());
    }

    protected function casts(): array
    {
        return [
            'request' => 'encrypted:array',
        ];
    }
}
