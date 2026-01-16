<?php

declare(strict_types=1);

namespace He4rt\Teams\Actions\NewMember;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use JsonSerializable;

class InviteTeamMemberDTO implements JsonSerializable
{
    public function __construct(
        public string $teamId,
        public string $name,
        public string $email,
    ) {}

    /**
     * @param  array{name: string, email: string, team_id: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            teamId: $data['team_id'],
            name: $data['name'],
            email: $data['email'],
        );
    }

    /**
     * @return array{name: string, email: string, password: mixed}
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make((string) Date::now()->getTimestamp()),
        ];
    }
}
