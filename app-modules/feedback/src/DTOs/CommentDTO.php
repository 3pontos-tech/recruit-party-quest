<?php

declare(strict_types=1);

namespace He4rt\Feedback\DTOs;

final readonly class CommentDTO
{
    public function __construct(
        public string $teamId,
        public string $applicationId,
        public string $authorId,
        public string $content,
        public bool $isInternal = true,
    ) {}

    public static function make(array $data): self
    {
        return new self(
            teamId: $data['team_id'],
            applicationId: $data['application_id'],
            authorId: $data['author_id'],
            content: $data['content'],
            isInternal: $data['is_internal'],
        );
    }
}
