<?php

declare(strict_types=1);

use He4rt\Screening\Collections\ScreeningResponseCollection;
use He4rt\Screening\DTOs\ScreeningResponseDTO;

/**
 * @param  array<string, mixed>  $override
 * @return array<string, mixed>
 */
function makeResponseData(array $override = []): array
{
    return array_merge([
        'teamId' => 'team-1',
        'applicationId' => 'app-1',
        'questionId' => 'q-1',
        'response_value' => ['value' => 'yes'],
    ], $override);
}

describe('ScreeningResponseCollection', function (): void {
    describe('constructor', function (): void {
        it('creates an empty collection when no arguments are passed', function (): void {
            $collection = new ScreeningResponseCollection();

            expect(iterator_to_array($collection))->toBeEmpty();
        });

        it('creates collection from an array of existing DTOs', function (): void {
            $dto1 = new ScreeningResponseDTO('team-1', 'app-1', 'q-1', ['value' => 'yes']);
            $dto2 = new ScreeningResponseDTO('team-1', 'app-1', 'q-2', ['value' => 'no']);

            $collection = new ScreeningResponseCollection([$dto1, $dto2]);

            expect(iterator_to_array($collection))->toHaveCount(2);
        });
    });

    describe('fromArray()', function (): void {
        it('returns an empty collection for an empty array', function (): void {
            $collection = ScreeningResponseCollection::fromArray([]);

            expect(iterator_to_array($collection))->toBeEmpty();
        });

        it('creates the correct number of DTOs from raw arrays', function (): void {
            $collection = ScreeningResponseCollection::fromArray([
                makeResponseData(['questionId' => 'q-1']),
                makeResponseData(['questionId' => 'q-2']),
                makeResponseData(['questionId' => 'q-3']),
            ]);

            expect(iterator_to_array($collection))->toHaveCount(3);
        });

        it('creates DTOs with correct data from raw arrays', function (): void {
            $collection = ScreeningResponseCollection::fromArray([
                makeResponseData(['questionId' => 'q-42', 'response_value' => ['value' => 'no']]),
            ]);

            $items = iterator_to_array($collection);

            expect($items[0])->toBeInstanceOf(ScreeningResponseDTO::class)
                ->and($items[0]->questionId)->toBe('q-42')
                ->and($items[0]->response_value)->toBe(['value' => 'no']);
        });

        it('normalizes scalar response_value via make() during fromArray()', function (): void {
            // Testa a integração entre fromArray() e ScreeningResponseDTO::make()
            $collection = ScreeningResponseCollection::fromArray([
                makeResponseData(['response_value' => 'yes']),
            ]);

            $items = iterator_to_array($collection);

            expect($items[0]->response_value)->toBe(['value' => 'yes']);
        });

        it('creates distinct DTO instances for each item', function (): void {
            $collection = ScreeningResponseCollection::fromArray([
                makeResponseData(['questionId' => 'q-1']),
                makeResponseData(['questionId' => 'q-2']),
            ]);

            $items = iterator_to_array($collection);

            expect($items[0])->not->toBe($items[1]);
        });
    });

    describe('add()', function (): void {
        it('appends a DTO to an empty collection', function (): void {
            $collection = new ScreeningResponseCollection();
            $dto = new ScreeningResponseDTO('team-1', 'app-1', 'q-1', ['value' => 'yes']);

            $collection->add($dto);

            expect(iterator_to_array($collection))->toHaveCount(1);
        });

        it('appends multiple DTOs sequentially', function (): void {
            $collection = new ScreeningResponseCollection();
            $collection->add(new ScreeningResponseDTO('team-1', 'app-1', 'q-1', ['value' => 'yes']));
            $collection->add(new ScreeningResponseDTO('team-1', 'app-1', 'q-2', ['value' => 'no']));

            expect(iterator_to_array($collection))->toHaveCount(2);
        });
    });

    describe('getIterator()', function (): void {
        it('returns an ArrayIterator instance', function (): void {
            $collection = ScreeningResponseCollection::fromArray([makeResponseData()]);

            expect($collection->getIterator())->toBeInstanceOf(ArrayIterator::class);
        });

        it('allows foreach traversal yielding ScreeningResponseDTO instances', function (): void {
            $collection = ScreeningResponseCollection::fromArray([
                makeResponseData(['questionId' => 'q-1']),
                makeResponseData(['questionId' => 'q-2']),
            ]);

            $visited = [];
            foreach ($collection as $dto) {
                expect($dto)->toBeInstanceOf(ScreeningResponseDTO::class);
                $visited[] = $dto->questionId;
            }

            expect($visited)->toBe(['q-1', 'q-2']);
        });

        it('preserves insertion order during traversal', function (): void {
            $collection = ScreeningResponseCollection::fromArray([
                makeResponseData(['questionId' => 'first']),
                makeResponseData(['questionId' => 'second']),
                makeResponseData(['questionId' => 'third']),
            ]);

            $ids = array_map(
                fn (ScreeningResponseDTO $dto): string => $dto->questionId,
                iterator_to_array($collection),
            );

            expect($ids)->toBe(['first', 'second', 'third']);
        });

        it('does not iterate over an empty collection', function (): void {
            $collection = new ScreeningResponseCollection();
            $count = 0;

            foreach ($collection as $dto) {
                $count++;
            }

            expect($count)->toBe(0);
        });
    });

    describe('jsonSerialize()', function (): void {
        it('returns an empty array for an empty collection', function (): void {
            expect(new ScreeningResponseCollection()->jsonSerialize())->toBe([]);
        });

        it('returns an array of ScreeningResponseDTO instances', function (): void {
            $collection = ScreeningResponseCollection::fromArray([makeResponseData()]);
            $serialized = $collection->jsonSerialize();

            expect($serialized)->toBeArray()
                ->and($serialized[0])->toBeInstanceOf(ScreeningResponseDTO::class);
        });

        it('produces valid JSON via json_encode on empty collection', function (): void {
            expect(json_encode(new ScreeningResponseCollection()))->toBe('[]');
        });

        it('round-trips correctly via json_encode + json_decode', function (): void {
            $collection = ScreeningResponseCollection::fromArray([
                makeResponseData([
                    'teamId' => 'team-99',
                    'applicationId' => 'app-99',
                    'questionId' => 'q-99',
                    'response_value' => ['value' => 'yes'],
                ]),
            ]);

            $decoded = json_decode(json_encode($collection), true);

            expect($decoded)->toHaveCount(1)
                ->and($decoded[0]['teamId'])->toBe('team-99')
                ->and($decoded[0]['applicationId'])->toBe('app-99')
                ->and($decoded[0]['questionId'])->toBe('q-99')
                ->and($decoded[0]['response_value'])->toBe(['value' => 'yes']);
        });

        it('preserves multiple DTOs with different response_value types in round-trip', function (): void {
            $collection = ScreeningResponseCollection::fromArray([
                makeResponseData(['questionId' => 'q-1', 'response_value' => 'yes']),
                makeResponseData(['questionId' => 'q-2', 'response_value' => ['php', 'go']]),
            ]);

            $decoded = json_decode(json_encode($collection), true);

            expect($decoded)->toHaveCount(2)
                ->and($decoded[0]['questionId'])->toBe('q-1')
                ->and($decoded[0]['response_value'])->toBe(['value' => 'yes'])
                ->and($decoded[1]['questionId'])->toBe('q-2')
                ->and($decoded[1]['response_value'])->toBe(['php', 'go']);
        });
    });
});
