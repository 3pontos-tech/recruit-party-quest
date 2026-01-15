<?php

declare(strict_types=1);

namespace He4rt\Applications\Models;

use App\Models\BaseModel;
use He4rt\Applications\Database\Factories\ApplicationStageHistoryFactory;
use He4rt\Applications\Policies\ApplicationStageHistoryPolicy;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $application_id
 * @property string|null $from_stage_id
 * @property string|null $to_stage_id
 * @property string|null $moved_by
 * @property string|null $notes
 * @property Carbon $created_at
 *
 * @extends BaseModel<ApplicationStageHistoryFactory>
 */
#[UsePolicy(ApplicationStageHistoryPolicy::class)]
#[UseFactory(ApplicationStageHistoryFactory::class)]
class ApplicationStageHistory extends BaseModel
{
    public const UPDATED_AT = null;

    protected $table = 'application_stage_history';

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
    public function fromStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'from_stage_id');
    }

    /**
     * @return BelongsTo<Stage, $this>
     */
    public function toStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'to_stage_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function movedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by');
    }
}
