<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Users\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $authorType = (new User)->getMorphClass();
        $commentableType = (new Application)->getMorphClass();

        $comments = DB::table('application_comments')->get();

        foreach ($comments as $comment) {
            $applicationExists = DB::table('applications')
                ->where('id', $comment->application_id)
                ->exists();

            if (! $applicationExists) {
                continue;
            }

            DB::table('comments')->insert([
                'id' => Str::uuid()->toString(),
                'author_id' => $comment->author_id,
                'author_type' => $authorType,
                'commentable_id' => $comment->application_id,
                'commentable_type' => $commentableType,
                'body' => $comment->content,
                'is_internal' => $comment->is_internal ?? true,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        // One-way data backfill: rows in `comments` are not distinguishable
        // from natively created ones, so there is nothing safe to reverse.
    }
};
