<?php

declare(strict_types=1);

namespace He4rt\Term\Actions;

use He4rt\Term\Models\Term;
use Illuminate\Database\Eloquent\Collection;

final class GetActiveTerms
{
    /**
     * Retrieve the active terms ordered by title.
     *
     * @return Collection<int, Term>
     */
    public function execute(): Collection
    {
        return Term::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
    }
}
