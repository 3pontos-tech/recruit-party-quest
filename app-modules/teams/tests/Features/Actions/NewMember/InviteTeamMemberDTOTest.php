<?php

declare(strict_types=1);

use He4rt\Teams\Actions\NewMember\InviteTeamMemberDTO;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;

test('it can be instantiated from an array', function (): void {
    $data = [
        'team_id' => 'team-123',
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ];

    $dto = InviteTeamMemberDTO::fromArray($data);

    expect($dto->teamId)->toBe('team-123')
        ->and($dto->name)->toBe('John Doe')
        ->and($dto->email)->toBe('john@example.com');
});

test('it serializes to json for user creation', function (): void {
    $dto = new InviteTeamMemberDTO('team-123', 'John Doe', 'john@example.com');

    $serialized = $dto->jsonSerialize();

    expect($serialized)->toHaveKey('name', 'John Doe')
        ->and($serialized)->toHaveKey('email', 'john@example.com')
        ->and($serialized)->toHaveKey('password');

    expect(Hash::check((string) Date::now()->getTimestamp(), $serialized['password']))->toBeTrue();
});
