<?php

declare(strict_types=1);

use He4rt\Screening\DTOs\ScreeningResponseDTO;

/**
 * @param  array<string, mixed>  $override
 * @return array<string, mixed>
 */
function makeDTOData(array $override = []): array
{
    return array_merge([
        'teamId' => 'team-1',
        'applicationId' => 'app-1',
        'questionId' => 'q-1',
        'response_value' => ['value' => 'yes'],
    ], $override);
}

describe('ScreeningResponseDTO', function (): void {
    describe('make()', function (): void {
        it('maps all fields correctly from a valid array', function (): void {
            $dto = ScreeningResponseDTO::make(makeDTOData([
                'teamId' => 'team-abc',
                'applicationId' => 'app-abc',
                'questionId' => 'q-abc',
            ]));

            expect($dto)->toBeInstanceOf(ScreeningResponseDTO::class)
                ->and($dto->teamId)->toBe('team-abc')
                ->and($dto->applicationId)->toBe('app-abc')
                ->and($dto->questionId)->toBe('q-abc');
        });

        it('keeps response_value as-is when it is already an array', function (): void {
            $dto = ScreeningResponseDTO::make(makeDTOData([
                'response_value' => ['php', 'go', 'rust'],
            ]));

            expect($dto->response_value)->toBe(['php', 'go', 'rust']);
        });

        it('keeps associative array response_value as-is', function (): void {
            $dto = ScreeningResponseDTO::make(makeDTOData([
                'response_value' => ['value' => 'yes'],
            ]));

            expect($dto->response_value)->toBe(['value' => 'yes']);
        });

        it('wraps string scalar response_value in value key', function (): void {
            // Respostas YesNo chegam como string 'yes'/'no' e devem ser normalizadas
            $dto = ScreeningResponseDTO::make(makeDTOData([
                'response_value' => 'yes',
            ]));

            expect($dto->response_value)->toBe(['value' => 'yes']);
        });

        it('wraps integer scalar response_value in value key', function (): void {
            $dto = ScreeningResponseDTO::make(makeDTOData([
                'response_value' => 42,
            ]));

            expect($dto->response_value)->toBe(['value' => 42]);
        });
    });

    describe('constructor', function (): void {
        it('assigns all properties directly', function (): void {
            $dto = new ScreeningResponseDTO(
                teamId: 'team-x',
                applicationId: 'app-x',
                questionId: 'q-x',
                response_value: ['value' => 'no'],
            );

            expect($dto->teamId)->toBe('team-x')
                ->and($dto->applicationId)->toBe('app-x')
                ->and($dto->questionId)->toBe('q-x')
                ->and($dto->response_value)->toBe(['value' => 'no']);
        });
    });
});
