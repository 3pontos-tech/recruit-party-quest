<?php

declare(strict_types=1);

namespace He4rt\Ai\Models;

// use He4rt\Team\Models\Team;
use App\Models\BaseModel;
use He4rt\Ai\Enums\AiPromptMessageType;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperPrompt
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
