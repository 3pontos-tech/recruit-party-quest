<?php

declare(strict_types=1);

namespace He4rt\Organization\Listeners;

use He4rt\Applications\Models\Application;
use He4rt\Feedback\Models\Comment;
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
        $comment = $event->comment;
        $mentionedUser = $event->user;

        if (! $comment instanceof Comment || ! $mentionedUser instanceof User) {
            return;
        }

        $comment->load(['author', 'commentable.candidate.user', 'commentable.team']);

        $application = $comment->commentable;

        if (! $application instanceof Application) {
            return;
        }

        $mentionedUser->notify(new MentionedInCommentNotification(
            comment: $comment,
            mentionedUser: $mentionedUser,
            tenantSlug: $application->team->slug,
        ));
    }
}
