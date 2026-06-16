<?php

declare(strict_types=1);

use He4rt\Feedback\Models\Comment;
use He4rt\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Kirschbaum\Commentions\CommentReaction;

// O model CommentReaction vem do pacote e não usa HasUuids, embora a coluna `id`
// seja UUID NOT NULL sem default. O FeedbackServiceProvider registra um hook
// `creating` que injeta o UUID — sem ele, o insert violaria o NOT NULL e falharia.
// A verificação é feita contra o valor persistido porque o id em memória é coagido
// a int (o model herda incrementing=true / keyType=int).
it('assigns a uuid id to a comment reaction on creation', function (): void {
    $user = User::factory()->create();
    $comment = Comment::factory()->create();

    CommentReaction::query()->create([
        'comment_id' => $comment->getKey(),
        'reactor_id' => $user->getKey(),
        'reactor_type' => $user->getMorphClass(),
        'reaction' => '👍',
    ]);

    $storedId = DB::table('comment_reactions')
        ->where('comment_id', $comment->getKey())
        ->value('id');

    expect($storedId)->toBeString()
        ->and(Str::isUuid($storedId))->toBeTrue();
});
