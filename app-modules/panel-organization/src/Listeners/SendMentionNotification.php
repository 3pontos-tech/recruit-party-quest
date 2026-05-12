<?php

declare(strict_types=1);

namespace He4rt\Organization\Listeners;

use He4rt\Organization\Notifications\MentionedInCommentNotification;
use He4rt\Users\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Kirschbaum\Commentions\Events\UserWasMentionedEvent;

final class SendMentionNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserWasMentionedEvent $event): void
    {
        $comment = $event->comment->load(['author', 'commentable.candidate.user', 'commentable.team']);
        $tenantSlug = $comment->commentable->team->slug;

        /** @var User $mentionedUser */
        $mentionedUser = $event->user;

        $mentionedUser->notify(new MentionedInCommentNotification(
            comment: $comment,
            mentionedUser: $mentionedUser,
            tenantSlug: $tenantSlug,
        ));
    }
}
