<?php

declare(strict_types=1);

namespace He4rt\Applications\Notifications;

use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Support\Icons\Heroicon;
use He4rt\Applications\Mail\ApplicationReceivedMail;
use He4rt\Applications\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class ApplicationReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): ApplicationReceivedMail
    {
        return new ApplicationReceivedMail($this->application)
            ->to($this->application->candidate->user->email);
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('applications::filament.notifications.application_received.title'))
            ->body(__('applications::filament.notifications.application_received.body', [
                'job' => $this->application->requisition->postTitle()
                    ?? __('applications::filament.emails.application_received.job_fallback'),
            ]))
            ->icon(Heroicon::OutlinedCheckCircle)
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(route('filament.app.resources.applications.view', [
                        'record' => $this->application->getKey(),
                    ]))
                    ->label(__('applications::filament.notifications.application_received.view_button'))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
