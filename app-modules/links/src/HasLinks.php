<?php

declare(strict_types=1);

namespace He4rt\Links;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasLinks
{
    /**
     * @return MorphToMany<Link, $this, MorphPivot>
     */
    public function links(): MorphToMany
    {
        return $this->morphToMany(
            related: Link::class,
            name: 'linkable',
            table: 'linkables',
        )
            ->orderBy('order_column');
    }

    public function attachLink(Link $link): void
    {
        $this->links()->syncWithoutDetaching($link);
    }

    public function detachLink(Link $link): void
    {
        $this->links()->detach($link);
    }

    /**
     * @param  array<int, int|array<string, mixed>>  $links
     */
    public function syncLinks(array $links): void
    {
        $this->links()->sync($links);
    }
}
