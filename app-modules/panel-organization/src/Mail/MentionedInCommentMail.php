<?php

declare(strict_types=1);

namespace He4rt\Organization\Mail;

use He4rt\Applications\Models\Application;
use He4rt\Feedback\Models\Comment;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\ApplicationResource;
use He4rt\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class MentionedInCommentMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Comment $comment,
        public User $mentionedUser,
        public string $tenantSlug,
    ) {}

    public function envelope(): Envelope
    {
        /** @var User $author */
        $author = $this->comment->author;

        return new Envelope(
            subject: __('panel-organization::filament.emails.mention.subject', [
                'author' => $author->name,
            ]),
        );
    }

    public function content(): Content
    {
        /** @var Application $application */
        $application = $this->comment->commentable;
        $candidateName = $application->candidate?->user->name ?? '';
        $url = ApplicationResource::getUrl('view', [
            'record' => $application->getKey(),
            'tenant' => $this->tenantSlug,
            'tab' => 'comments',
        ], panel: 'organization');

        return new Content(
            markdown: 'panel-organization::emails.mentioned-in-comment',
            with: [
                'comment' => $this->comment,
                'mentionedUser' => $this->mentionedUser,
                'candidateName' => $candidateName,
                'url' => $url,
            ],
        );
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return [];
    }
}
