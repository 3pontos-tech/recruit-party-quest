<?php

declare(strict_types=1);

use He4rt\Links\Link;
use He4rt\Teams\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can attach links to a team', function (): void {
    // Setup
    $team = Team::factory()->create();
    $data = Link::factory()->make()->toArray();

    // Action
    $team->links()->create($data);

    // Assert
    expect($team->links)->toHaveCount(1)
        ->and($team->links->first()->name)->toBe($data['name'])
        ->and($team->links->first()->url)->toBe($data['url']);
});

it('orders links by order_column', function (): void {
    $team = Team::factory()->create();

    $link1 = $team->links()->create(Link::factory()->raw(['order_column' => 2, 'name' => 'Second']));
    $link2 = $team->links()->create(Link::factory()->raw(['order_column' => 1, 'name' => 'First']));

    expect($team->refresh()->links->first()->name)->toBe('First')
        ->and($team->links->last()->name)->toBe('Second');
});
