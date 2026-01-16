<?php

declare(strict_types=1);

namespace He4rt\Feedback\Models;

use App\Models\BaseModel;
use He4rt\Applications\Models\Application;
use He4rt\Feedback\Database\Factories\EvaluationFactory;
use He4rt\Feedback\Enums\EvaluationRatingEnum;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Teams\Concerns\BelongsToTeam;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $application_id
 * @property string $stage_id
 * @property string $evaluator_id
 * @property EvaluationRatingEnum $overall_rating
 * @property string|null $recommendation
 * @property string|null $strengths
 * @property string|null $concerns
 * @property string|null $notes
 * @property array<string, mixed> $criteria_scores
 * @property Carbon|null $submitted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Application $application
 * @property-read Stage $stage
 * @property-read User $evaluator
 *
 * @extends BaseModel<EvaluationFactory>
 */
#[UseFactory(EvaluationFactory::class)]
class Evaluation extends BaseModel
{
    use BelongsToTeam;

    protected $table = 'evaluations';

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * @return BelongsTo<Stage, $this>
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    protected function casts(): array
    {
        return [
            'overall_rating' => EvaluationRatingEnum::class,
            'criteria_scores' => 'array',
            'submitted_at' => 'datetime',
        ];
    }
}
