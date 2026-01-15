<?php

declare(strict_types=1);

use He4rt\Location\Address;
use He4rt\Teams\Team;
use He4rt\Users\User;

it('can create an address', function (): void {
    $address = Address::factory()->create();

    expect($address)->toBeInstanceOf(Address::class)
        ->and($address->id)->not->toBeNull()
        ->and($address->full_address)->not->toBeNull()
        ->and($address->city)->not->toBeNull()
        ->and($address->country)->not->toBeNull();
});

it('has morph relationship to addressable', function (): void {
    $user = User::factory()->create();
    $address = Address::factory()->create([
        'addressable_id' => $user->id,
        'addressable_type' => User::class,
    ]);

    expect($address->addressable)->toBeInstanceOf(User::class)
        ->and($address->addressable->id)->toBe($user->id);
});

it('can be associated with a team', function (): void {
    $team = Team::factory()->create();
    $address = Address::factory()->create([
        'addressable_id' => $team->id,
        'addressable_type' => Team::class,
    ]);

    expect($address->addressable)->toBeInstanceOf(Team::class)
        ->and($address->addressable->id)->toBe($team->id);
});

it('has latitude and longitude coordinates', function (): void {
    $address = Address::factory()->create([
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    ]);

    expect($address->latitude)->toBe(40.7128)
        ->and($address->longitude)->toBe(-74.0060);
});

it('casts latitude to float', function (): void {
    $address = Address::factory()->create(['latitude' => 40.7128]);

    expect($address->latitude)->toBeFloat();
});

it('casts longitude to float', function (): void {
    $address = Address::factory()->create(['longitude' => -74.0060]);

    expect($address->longitude)->toBeFloat();
});
