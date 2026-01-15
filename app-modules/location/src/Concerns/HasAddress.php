<?php

declare(strict_types=1);

namespace He4rt\Location\Concerns;

use He4rt\Location\Address;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasAddress
{
    /**
     * @return MorphOne<Address, $this>
     */
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }
}
