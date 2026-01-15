<?php

declare(strict_types=1);

namespace He4rt\Screening\Models;

use App\Models\BaseModel;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Database\Factories\ScreeningQuestionFactory;
use He4rt\Screening\Enums\QuestionTypeEnum;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $requisition_id
 * @property string $question_text
 * @property QuestionTypeEnum $question_type
 * @property array<int, mixed>|null $choices
 * @property bool $is_required
 * @property bool $is_knockout
 * @property array<string, mixed>|null $knockout_criteria
 * @property int $display_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read JobRequisition $requisition
 * @property-read Collection<int, ScreeningResponse> $responses
 *
 * @extends BaseModel<ScreeningQuestionFactory>
 */
#[UseFactory(ScreeningQuestionFactory::class)]
class ScreeningQuestion extends BaseModel
{
    protected $table = 'screening_questions';

    /**
     * @return BelongsTo<JobRequisition, $this>
     */
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(JobRequisition::class, 'requisition_id');
    }

    /**
     * @return HasMany<ScreeningResponse, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(ScreeningResponse::class, 'question_id');
    }

    protected function casts(): array
    {
        return [
            'question_type' => QuestionTypeEnum::class,
            'choices' => 'array',
            'is_required' => 'boolean',
            'is_knockout' => 'boolean',
            'knockout_criteria' => 'array',
        ];
    }
}
