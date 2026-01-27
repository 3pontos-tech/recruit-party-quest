<?php

declare(strict_types=1);

namespace He4rt\Links;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class Link extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'name',
        'slug',
        'url',
        'icon',
        'type',
        'order_column',
    ];

    /**
     * @return MorphToMany<Model, $this, Pivot>
     */
    public function linkables(): MorphToMany
    {
        return $this->morphedByMany(
            related: Model::class,
            name: 'linkable',
        );
    }

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'slug' => 'array',
            'url' => 'array',
        ];
    }
}
