<?php

declare(strict_types=1);

namespace He4rt\Location\Concerns;

use He4rt\Location\Address;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAddresses
{
    /**
     * @return MorphMany<Address, $this>
     */
    public function address(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }
}
