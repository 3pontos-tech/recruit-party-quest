<?php

declare(strict_types=1);

namespace He4rt\Links\Traits;

use He4rt\Links\Link;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

trait HasLinks
{
    /**
     * @return MorphToMany<Link, $this, Pivot>
     */
    public function links(): MorphToMany
    {
        return $this->morphToMany(
            related: Link::class,
            name: 'linkable',
            table: 'linkables',
        )
            ->withTimestamps()
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

    public function syncLinks(array $links): void
    {
        $this->links()->sync($links);
    }
}
