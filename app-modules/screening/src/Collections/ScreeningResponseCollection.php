<?php

declare(strict_types=1);

namespace He4rt\Screening\Collections;

use ArrayIterator;
use He4rt\Screening\DTOs\ScreeningResponseDTO;
use IteratorAggregate;
use JsonSerializable;

/**
 * @implements IteratorAggregate<int, ScreeningResponseDTO>
 */
final class ScreeningResponseCollection implements IteratorAggregate, JsonSerializable
{
    /**
     * @var ScreeningResponseDTO[]
     */
    private array $responses = [];

    /**
     * @param  ScreeningResponseDTO[]  $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $item) {
            $this->add($item);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     */
    public static function fromArray(array $data): self
    {
        $collection = new self();

        foreach ($data as $item) {
            $collection->add(ScreeningResponseDTO::make($item));
        }

        return $collection;
    }

    public function add(ScreeningResponseDTO $response): void
    {
        $this->responses[] = $response;
    }

    /**
     * @return ArrayIterator<int, ScreeningResponseDTO>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->responses);
    }

    /**
     * @return ScreeningResponseDTO[]
     */
    public function jsonSerialize(): array
    {
        return $this->responses;
    }
}
