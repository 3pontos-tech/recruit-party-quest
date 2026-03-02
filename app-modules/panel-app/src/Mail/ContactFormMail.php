<?php

declare(strict_types=1);

namespace He4rt\App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactFormMail extends Mailable
{
    public function __construct(
        public readonly string $senderName,
        public readonly string $senderEmail,
        public readonly ?string $senderPhone,
        public readonly string $messageBody,
        public readonly string $sentAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [$this->senderEmail],
            subject: 'Novo contato via site – Fale Conosco',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'panel-app::mail.contact-form',
        );
    }
}
