<?php

declare(strict_types=1);

namespace He4rt\Applications\Mail;

use He4rt\Applications\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Application $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('applications::filament.emails.application_received.subject', [
                'job' => $this->application->requisition->post->title,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'applications::emails.application-received',
            with: [
                'candidateName' => $this->application->candidate->user->name,
                'jobTitle' => $this->application->requisition->post->title,
                // Rota nomeada (string) em vez de panel-app::ApplicationResource::getUrl()
                // para não criar dependência reversa applications -> panel-app.
                'url' => route('filament.app.resources.applications.view', [
                    'record' => $this->application->getKey(),
                ]),
            ],
        );
    }
}
