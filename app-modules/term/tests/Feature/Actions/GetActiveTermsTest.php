<?php

declare(strict_types=1);

use He4rt\Term\Actions\GetActiveTerms;
use He4rt\Term\Models\Term;

it('returns only active terms ordered by title', function (): void {
    $zebra = Term::factory()->create(['title' => 'Zebra Policy', 'slug' => 'zebra-policy']);
    $alpha = Term::factory()->create(['title' => 'Alpha Policy', 'slug' => 'alpha-policy']);
    Term::factory()->inactive()->create(['title' => 'Hidden Policy', 'slug' => 'hidden-policy']);

    $terms = resolve(GetActiveTerms::class)->execute();

    expect($terms->pluck('title')->all())->toBe(['Alpha Policy', 'Zebra Policy'])
        ->and($terms->pluck('id')->all())->toBe([$alpha->id, $zebra->id]);
});

it('returns an empty collection when there are no active terms', function (): void {
    Term::factory()->inactive()->create();

    expect(resolve(GetActiveTerms::class)->execute())->toBeEmpty();
});
