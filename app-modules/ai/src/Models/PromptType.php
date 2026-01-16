<?php

declare(strict_types=1);

namespace He4rt\Ai\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperPromptType
 */
final class PromptType extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
    ];

    /**
     * @return HasMany<Prompt, $this>
     */
    public function prompts(): HasMany
    {
        return $this->hasMany(Prompt::class, 'type_id');
    }
}
