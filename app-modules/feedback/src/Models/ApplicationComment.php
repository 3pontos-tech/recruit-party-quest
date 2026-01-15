<?php

declare(strict_types=1);

namespace He4rt\Feedback\Models;

use App\Models\BaseModel;
use He4rt\Applications\Models\Application;
use He4rt\Feedback\Database\Factories\ApplicationCommentFactory;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $application_id
 * @property string $author_id
 * @property string $content
 * @property bool $is_internal
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Application $application
 * @property-read User $author
 *
 * @extends BaseModel<ApplicationCommentFactory>
 */
#[UseFactory(ApplicationCommentFactory::class)]
class ApplicationComment extends BaseModel
{
    protected $table = 'application_comments';

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
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }
}
