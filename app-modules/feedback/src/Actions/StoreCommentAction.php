<?php

declare(strict_types=1);

namespace He4rt\Feedback\Actions;

use He4rt\Feedback\DTOs\CommentDTO;
use He4rt\Feedback\Models\ApplicationComment;

final class StoreCommentAction
{
    public function execute(CommentDTO $dto): void
    {
        ApplicationComment::query()->create([
            'team_id' => $dto->teamId,
            'application_id' => $dto->applicationId,
            'author_id' => $dto->authorId,
            'content' => $dto->content,
            'is_internal' => $dto->isInternal,
        ]);
    }
}
