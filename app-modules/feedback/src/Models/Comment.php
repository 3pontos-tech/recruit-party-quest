<?php

declare(strict_types=1);

namespace He4rt\Feedback\Models;

use He4rt\Feedback\Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Config;

class Comment extends \Kirschbaum\Commentions\Comment
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;
    use HasUuids;

    public function getMentioned(): Collection
    {
        $commenterModel = Config::getCommenterModel();

        preg_match_all(
            '/<span[^>]*data-type="mention"[^>]*data-id="([^"]+)"[^>]*>/',
            $this->body,
            $matches
        );

        $ids = collect($matches[1] ?? [])->unique()->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return $commenterModel::query()
            ->whereIn((new $commenterModel)->getKeyName(), $ids)
            ->get();
    }
}
