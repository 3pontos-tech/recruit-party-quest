<?php

declare(strict_types=1);

use He4rt\Term\Models\Term;
use Illuminate\Database\QueryException;

test('term factory creates valid term', function (): void {
    $term = Term::factory()->create();

    expect($term)->toBeInstanceOf(Term::class)
        ->and($term->title)->not->toBeEmpty()
        ->and($term->slug)->not->toBeEmpty()
        ->and($term->is_active)->toBeTrue()
        ->and($term->content)->toBeArray();
});

test('term content is cast to array', function (): void {
    $sections = [
        [
            'id' => 'intro',
            'title' => 'Introduction',
            'body' => '<p>Hello</p>',
            'show_in_sidebar' => true,
        ],
    ];

    $term = Term::factory()->create(['content' => $sections]);
    $term->refresh();

    expect($term->content)->toBeArray()
        ->and($term->content)->toHaveCount(1)
        ->and($term->content[0]['id'])->toBe('intro')
        ->and($term->content[0]['title'])->toBe('Introduction');
});

test('term slug must be unique', function (): void {
    Term::factory()->create(['slug' => 'terms-of-service']);

    expect(fn () => Term::factory()->create(['slug' => 'terms-of-service']))
        ->toThrow(QueryException::class);
});

test('term can be soft deleted', function (): void {
    $term = Term::factory()->create();
    $term->delete();

    expect($term->trashed())->toBeTrue()
        ->and(Term::query()->count())->toBe(0)
        ->and(Term::withTrashed()->count())->toBe(1);
});

test('term inactive state works', function (): void {
    $term = Term::factory()->inactive()->create();

    expect($term->is_active)->toBeFalse();
});

test('term can be created with null content', function (): void {
    $term = Term::factory()->create(['content' => null]);

    expect($term->content)->toBeNull();
});
