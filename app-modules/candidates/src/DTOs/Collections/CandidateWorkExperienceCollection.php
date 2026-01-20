<?php

declare(strict_types=1);

namespace He4rt\Candidates\DTOs\Collections;

use ArrayIterator;
use He4rt\Candidates\DTOs\CandidateWorkExperienceDTO;
use IteratorAggregate;
use JsonSerializable;

/**
 * @implements IteratorAggregate<int, CandidateWorkExperienceDTO>
 */
final class CandidateWorkExperienceCollection implements IteratorAggregate, JsonSerializable
{
    /**
     * @var CandidateWorkExperienceDTO[]
     */
    private array $experiences = [];

    /**
     * @param  CandidateWorkExperienceDTO[]  $data
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
            $collection->add(CandidateWorkExperienceDTO::make($item));
        }

        return $collection;
    }

    public function add(CandidateWorkExperienceDTO $dto): void
    {
        $this->experiences[] = $dto;
    }

    /**
     * @return ArrayIterator<int, CandidateWorkExperienceDTO>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->experiences);
    }

    /**
     * @return CandidateWorkExperienceDTO[]
     */
    public function jsonSerialize(): array
    {
        return $this->experiences;
    }
}
