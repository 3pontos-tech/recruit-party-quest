<?php

declare(strict_types=1);

namespace He4rt\Ai\Models;

// use He4rt\Team\Models\Team;
use App\Models\BaseModel;
use He4rt\Ai\Enums\AiPromptMessageType;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string|null $user_id
 * @property string $type_id
 * @property AiPromptMessageType $message_type
 * @property bool $is_smart
 * @property string $title
 * @property string|null $description
 * @property string $prompt
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read PromptType $type
 * @property-read User|null $user
 * @property-read Collection<int, AiAssistant> $assistants
 */
final class Prompt extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'message_type',
        'title',
        'description',
        'prompt',
        'is_smart',
    ];

    /**
     * @return BelongsTo<PromptType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(PromptType::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<AiAssistant, $this, Pivot>
     */
    public function assistants(): BelongsToMany
    {
        return $this->belongsToMany(AiAssistant::class, 'ai_assistants_prompts');
    }

    public function toLlm(): string
    {
        return sprintf("
            Topico: %s
            Descrição: %s
            Guideline: %s
            \n\n
        ", $this->title, $this->description, $this->prompt);
    }

    protected function casts(): array
    {
        return [
            'message_type' => AiPromptMessageType::class,
            'is_smart' => 'boolean',
        ];
    }
}
