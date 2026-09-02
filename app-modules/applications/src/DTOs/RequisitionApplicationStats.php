<?php

declare(strict_types=1);

namespace He4rt\Applications\DTOs;

/** Agregados de candidaturas de uma vaga para o espaço do recrutador. */
final readonly class RequisitionApplicationStats
{
    /**
     * @param  array<string, int>  $byStage
     */
    public function __construct(
        public int $total,
        public int $new,
        public int $active,
        public int $closed,
        public int $unseen,
        public int $overdue,
        public int $knockoutPassed,
        public int $knockoutFailed,
        public int $knockoutUnanswered,
        public array $byStage,
    ) {}

    public function openTotal(): int
    {
        return array_sum($this->byStage);
    }
}
