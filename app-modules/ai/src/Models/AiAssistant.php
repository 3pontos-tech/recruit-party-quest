<?php

declare(strict_types=1);

namespace He4rt\Ai\Models;

use App\Models\BaseModel;
use He4rt\Ai\Enums\AiAssistantApplication;
use He4rt\Ai\Enums\AiModel;
use He4rt\Ai\Exceptions\DefaultAssistantLockedPropertyException;
use He4rt\Ai\Observers\AiAssistantObserver;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

// use He4rt\Team\Models\Team;

#[ObservedBy([AiAssistantObserver::class])]
final class AiAssistant extends BaseModel implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'application',
        'model',
        'is_default',
        'description',
        'instructions',
        'knowledge',
        'owner_id',
        'archived_at',
    ];

    private ?bool $isUpvoted = null;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->acceptsFile(function (File $file): bool {
                throw_if($this->application === AiAssistantApplication::PersonalAssistant && $this->is_default, DefaultAssistantLockedPropertyException::class, 'avatar');

                return in_array($file->mimeType, [
                    'image/png',
                    'image/jpeg',
                    'image/gif',
                ]);
            });
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('avatar-height-250px')
            ->performOnCollections('avatar')
            ->height(250);

        $this->addMediaConversion('thumbnail')
            ->performOnCollections('avatar')
            ->width(32)
            ->height(32);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return HasMany<AiThread, $this>
     */
    public function threads(): HasMany
    {
        return $this->hasMany(AiThread::class, 'assistant_id');
    }

    public function isDefault(): bool
    {
        return $this->is_default ?? false;
    }

    /**
     * @return BelongsToMany<Prompt, $this, Pivot>
     */
    public function prompts(): BelongsToMany
    {
        return $this->belongsToMany(Prompt::class, 'ai_assistants_prompts');
    }

    protected function casts(): array
    {
        return [
            'application' => AiAssistantApplication::class,
            'archived_at' => 'datetime',
            'is_default' => 'bool',
            'model' => AiModel::class,
        ];
    }
}
