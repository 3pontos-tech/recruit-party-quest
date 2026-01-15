<?php

declare(strict_types=1);

namespace He4rt\Screening\Models;

use App\Models\BaseModel;
use He4rt\Applications\Models\Application;
use He4rt\Screening\Database\Factories\ScreeningResponseFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property string $application_id
 * @property string $question_id
 * @property Collection<int,string>|string|null $response_value
 * @property bool $is_knockout_fail
 * @property Carbon $created_at
 * @property-read Application $application
 * @property-read ScreeningQuestion $question
 *
 * @extends BaseModel<ScreeningResponseFactory>
 */
#[UseFactory(ScreeningResponseFactory::class)]
class ScreeningResponse extends BaseModel
{
    public $timestamps = false;

    protected $table = 'screening_responses';

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * @return BelongsTo<ScreeningQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(ScreeningQuestion::class, 'question_id');
    }

    protected function casts(): array
    {
        return [
            'response_value' => 'array',
            'is_knockout_fail' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
