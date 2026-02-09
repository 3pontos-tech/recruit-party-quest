<?php

declare(strict_types=1);

namespace He4rt\Term\Models;

use App\Models\BaseModel;
use He4rt\Term\Database\Factories\TermFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $title
 * @property string $slug
 * @property array<int, array{id: string, title: string, body: string, show_in_sidebar: bool}>|null $content
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @extends BaseModel<TermFactory>
 */
#[UseFactory(TermFactory::class)]
class Term extends BaseModel
{
    protected $table = 'terms';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
