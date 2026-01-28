<?php

declare(strict_types=1);

namespace He4rt\Links;

use He4rt\Links\Database\Factories\LinkFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[UseFactory(LinkFactory::class)]
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

    protected static function booted(): void
    {
        static::creating(static function (Link $link): void {
            if (filled($link->slug)) {
                return;
            }

            $name = $link->name ?? null;

            if (! $name) {
                return;
            }

            $link->slug = Str::slug($name);
        });
    }

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'slug' => 'array',
            'url' => 'array',
            'type' => LinkTypeEnum::class,
        ];
    }
}
