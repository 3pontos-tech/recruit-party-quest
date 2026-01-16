<?php

declare(strict_types=1);

namespace He4rt\Teams\Actions\NewMember;

use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public User $user,
        public Team $team,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('teams::filament.emails.team_invitation.subject', [
                'team_name' => $this->team->name,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'teams::emails.team-invitation',
            with: [
                'user' => $this->user,
                'team' => $this->team,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
