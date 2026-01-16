<?php

declare(strict_types=1);

namespace He4rt\Ai\Models;

use App\Models\BaseModel;
use He4rt\Ai\Casts\AsSession;
use He4rt\Ai\Models\Scopes\AiThreadScope;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Livewire\Wireable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

// use App\Settings\DisplaySettings;

/**
 * @mixin IdeHelperAiThread
 */
#[ScopedBy(AiThreadScope::class)]
final class AiThread extends BaseModel implements HasMedia, Wireable
{
    use InteractsWithMedia;
    use Prunable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'assistant_id',
        'folder_id',
        'user_id',
        'locked_at',
        'session',
    ];

    public static function fromLivewire($value)
    {
        return self::query()->find($value['id']);
    }

    /**
     * @return BelongsTo<AiAssistant, $this>
     */
    public function assistant(): BelongsTo
    {
        return $this->belongsTo(AiAssistant::class, 'assistant_id');
    }

    /**
     * @return BelongsTo<AiThreadFolder, $this>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(AiThreadFolder::class, 'folder_id');
    }

    /**
     * @return HasMany<AiMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'thread_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        /** @phpstan-ignore argument.templateType (We are using some Laravel magic here to use Tenant as a Pivot without actually being a pivot) */
        return $this->belongsToMany(
            User::class,
            table: 'ai_messages',
            foreignPivotKey: 'thread_id',
        )->using(AiMessage::class); // @phpstan-ignore argument.type (Same as above)
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prunable(): Builder
    {
        return self::query()
            ->whereNotNull('deleted_at')
            ->where('deleted_at', '<=', now()->subDays(7))
            ->whereDoesntHave('messages', fn (Builder $query) => $query->withTrashed());
    }

    public function toLivewire()
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('generated_images');
    }

    protected function casts(): array
    {
        return [
            'locked_at' => 'datetime',
            'saved_at' => 'datetime',
            'session' => AsSession::class,
        ];
    }
}
