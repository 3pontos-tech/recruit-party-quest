<?php

declare(strict_types=1);

namespace He4rt\Candidates\DTOs\Collections;

use ArrayIterator;
use He4rt\Candidates\DTOs\CandidateEducationDTO;
use IteratorAggregate;
use JsonSerializable;

/**
 * @implements IteratorAggregate<int, CandidateEducationDTO>
 */
final class CandidateEducationCollection implements IteratorAggregate, JsonSerializable
{
    /**
     * @var CandidateEducationDTO[]
     */
    private array $experiences = [];

    /**
     * @param  CandidateEducationDTO[]  $data
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
            $collection->add(CandidateEducationDTO::make($item));
        }

        return $collection;
    }

    public function add(CandidateEducationDTO $dto): void
    {
        $this->experiences[] = $dto;
    }

    /**
     * @return ArrayIterator<int, CandidateEducationDTO>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->experiences);
    }

    /**
     * @return CandidateEducationDTO[]
     */
    public function jsonSerialize(): array
    {
        return $this->experiences;
    }
}
