<?php

declare(strict_types=1);

namespace He4rt\Screening\DTOs;

final readonly class ScreeningResponseDTO
{
    /**
     * @param  array<string, mixed>  $response_value
     */
    public function __construct(
        public string $teamId,
        public string $applicationId,
        public string $questionId,
        public array $response_value,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data): self
    {
        return new self(
            teamId: $data['teamId'],
            applicationId: $data['applicationId'],
            questionId: $data['questionId'],
            response_value: is_array($data['response_value'])
                ? $data['response_value']
                : ['value' => $data['response_value']],
        );
    }
}
