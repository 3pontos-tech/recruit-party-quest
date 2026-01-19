<?php

declare(strict_types=1);

namespace He4rt\Screening\Models;

use App\Models\BaseModel;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Casts\SettingsCast;
use He4rt\Screening\Database\Factories\ScreeningQuestionFactory;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Teams\Concerns\BelongsToTeam;
use He4rt\Teams\Team;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $team_id
 * @property string $screenable_id
 * @property string $screenable_type
 * @property string $question_text
 * @property QuestionTypeEnum $question_type
 * @property array<string, mixed>|null $settings
 * @property bool $is_required
 * @property bool $is_knockout
 * @property array<string, mixed>|null $knockout_criteria
 * @property int $display_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read JobRequisition|Stage $screenable
 * @property-read Team $team
 * @property-read Collection<int, ScreeningResponse> $responses
 *
 * @extends BaseModel<ScreeningQuestionFactory>
 */
#[UseFactory(ScreeningQuestionFactory::class)]
class ScreeningQuestion extends BaseModel
{
    use BelongsToTeam;

    protected $table = 'screening_questions';

    /**
     * @return HasMany<ScreeningResponse, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(ScreeningResponse::class, 'question_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function screenable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return [
            'question_type' => QuestionTypeEnum::class,
            'settings' => SettingsCast::class,
            'is_required' => 'boolean',
            'is_knockout' => 'boolean',
            'knockout_criteria' => 'array',
        ];
    }
}
