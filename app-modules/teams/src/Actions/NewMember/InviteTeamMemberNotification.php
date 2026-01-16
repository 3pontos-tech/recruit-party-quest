<?php

declare(strict_types=1);

namespace He4rt\Teams\Actions\NewMember;

use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InviteTeamMemberNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public User $user,
        public Team $team,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): TeamInvitationMail
    {
        return new TeamInvitationMail(
            user: $this->user,
            team: $this->team,
        )->to($this->user->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [

        ];
    }
}
