<?php

declare(strict_types=1);

namespace He4rt\Links;

use App\Models\BaseModel;
use He4rt\Links\Database\Factories\LinkFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string|null $slug
 * @property string $name
 * @property string $url
 * @property string|null $icon
 *
 * @extends BaseModel<LinkFactory>
 */
#[UseFactory(LinkFactory::class)]
class Link extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'url',
        'icon',
        'type',
        'order_column',
    ];

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
            'type' => LinkTypeEnum::class,
        ];
    }
}
