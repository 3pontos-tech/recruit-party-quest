<?php

declare(strict_types=1);

namespace He4rt\Feedback\Models;

use He4rt\Feedback\Database\Factories\CommentFactory;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class Comment extends \Kirschbaum\Commentions\Comment
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;
    use HasUuids;

    /** @return Collection<int, User> */
    public function getMentioned(): Collection
    {
        preg_match_all(
            '/<span[^>]*data-type="mention"[^>]*data-id="([^"]+)"[^>]*>/',
            $this->body,
            $matches
        );

        $ids = collect($matches[1])->unique()->values();

        return User::query()
            ->whereIn((new User)->getKeyName(), $ids)
            ->get()
            ->toBase();
    }
}
