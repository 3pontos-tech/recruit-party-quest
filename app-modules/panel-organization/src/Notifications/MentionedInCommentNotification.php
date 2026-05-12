<?php

declare(strict_types=1);

namespace He4rt\Organization\Notifications;

use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Support\Icons\Heroicon;
use He4rt\Applications\Models\Application;
use He4rt\Feedback\Models\Comment;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\ApplicationResource;
use He4rt\Organization\Mail\MentionedInCommentMail;
use He4rt\Users\User;
use Illuminate\Notifications\Notification;

final class MentionedInCommentNotification extends Notification
{
    public function __construct(
        public Comment $comment,
        public User $mentionedUser,
        public string $tenantSlug,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MentionedInCommentMail
    {
        return new MentionedInCommentMail(
            comment: $this->comment,
            mentionedUser: $this->mentionedUser,
            tenantSlug: $this->tenantSlug,
        )->to($this->mentionedUser->email);
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        /** @var Application $application */
        $application = $this->comment->commentable;
        $candidateName = $application->candidate?->user->name ?? '';
        $url = ApplicationResource::getUrl('view', [
            'record' => $application->getKey(),
            'tenant' => $this->tenantSlug,
            'tab' => 'comments',
        ], panel: 'organization');

        /** @var User $author */
        $author = $this->comment->author;

        return FilamentNotification::make()
            ->title(__('panel-organization::filament.notifications.mention.title', [
                'author' => $author->name,
            ]))
            ->body(__('panel-organization::filament.notifications.mention.body', [
                'candidate' => $candidateName,
            ]))
            ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
            ->actions([
                Action::make('view')
                    ->button()
                    ->url($url)
                    ->label(__('panel-organization::filament.notifications.mention.view_button'))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
