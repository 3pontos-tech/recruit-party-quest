<?php

declare(strict_types=1);

use He4rt\Links\Link;
use He4rt\Teams\Team;

it('can attach links to a team via relationship', function (): void {
    $team = Team::factory()->create();

    $data = Link::factory()->make()->toArray();

    $team->links()->create($data);

    expect($team->refresh()->links)
        ->toHaveCount(1)
        ->and($team->links->first()->name)->toBe($data['name'])
        ->and($team->links->first()->url)->toBe($data['url']);
});

it('orders links by order_column', function (): void {
    $team = Team::factory()->create();

    $team->links()->create(
        Link::factory()->raw(['order_column' => 2, 'name' => 'Second'])
    );

    $team->links()->create(
        Link::factory()->raw(['order_column' => 1, 'name' => 'First'])
    );

    $team = $team->refresh();

    expect($team->links->first()->name)->toBe('First')
        ->and($team->links->last()->name)->toBe('Second');
});

it('can attach an existing link using attachLink()', function (): void {
    $team = Team::factory()->create();
    $link = Link::factory()->create();

    $team->attachLink($link);

    expect($team->refresh()->links)
        ->toHaveCount(1)
        ->and($team->links->first()->is($link))->toBeTrue();
});

it('does not duplicate links when attaching the same link twice', function (): void {
    $team = Team::factory()->create();
    $link = Link::factory()->create();

    $team->attachLink($link);
    $team->attachLink($link);

    expect($team->refresh()->links)->toHaveCount(1);
});

it('can detach a link using detachLink()', function (): void {
    $team = Team::factory()->create();
    $link = Link::factory()->create();

    $team->attachLink($link);
    expect($team->refresh()->links)->toHaveCount(1);

    $team->detachLink($link);

    expect($team->refresh()->links)->toHaveCount(0);
});

it('can sync links using syncLinks()', function (): void {
    $team = Team::factory()->create();

    $linkA = Link::factory()->create();
    $linkB = Link::factory()->create();
    $linkC = Link::factory()->create();

    // Initial sync
    $team->syncLinks([$linkA->id, $linkB->id]);

    expect($team->refresh()->links)
        ->toHaveCount(2)
        ->pluck('id')
        ->toContain($linkA->id, $linkB->id);

    // Resync (remove A, keep B, add C)
    $team->syncLinks([$linkB->id, $linkC->id]);

    expect($team->refresh()->links)
        ->toHaveCount(2)
        ->pluck('id')
        ->toContain($linkB->id, $linkC->id)
        ->not->toContain($linkA->id);
});
