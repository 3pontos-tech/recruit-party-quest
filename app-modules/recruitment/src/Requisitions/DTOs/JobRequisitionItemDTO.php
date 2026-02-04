<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\DTOs;

use He4rt\Recruitment\Requisitions\Enums\JobRequisitionItemTypeEnum;

final readonly class JobRequisitionItemDTO
{
    public function __construct(
        public JobRequisitionItemTypeEnum $type,
        public string $content,

    ) {}

    /**
     * @param  array{type:string, content:string, order?:int}  $data
     */
    public static function make(array $data): self
    {
        return new self(
            type: JobRequisitionItemTypeEnum::from($data['type']),
            content: $data['content'],
        );
    }
}
