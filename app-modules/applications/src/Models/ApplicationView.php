<?php

declare(strict_types=1);

namespace He4rt\Applications\Models;

use App\Models\BaseModel;
use He4rt\Applications\Database\Factories\ApplicationViewFactory;
use He4rt\Teams\Concerns\BelongsToTeam;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $team_id
 * @property string $application_id
 * @property string|null $viewed_by
 * @property Carbon $viewed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Application $application
 * @property-read User|null $viewer
 *
 * @extends BaseModel<ApplicationViewFactory>
 */
#[UseFactory(ApplicationViewFactory::class)]
class ApplicationView extends BaseModel
{
    use BelongsToTeam;

    protected $table = 'application_views';

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewed_by');
    }

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }
}
