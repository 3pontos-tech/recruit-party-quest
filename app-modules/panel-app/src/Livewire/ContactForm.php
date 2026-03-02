<?php

declare(strict_types=1);

namespace He4rt\App\Livewire;

use He4rt\App\Mail\ContactFormMail;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

class ContactForm extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public string $phone = '';

    #[Validate('required|string|min:10|max:2000')]
    public string $message = '';

    /**
     * Honeypot field — must remain empty; bots fill it.
     */
    #[Validate('max:0')]
    public string $honeypot = '';

    public bool $isLoading = false;

    public bool $isSent = false;

    public bool $hasError = false;

    public ?string $rateLimitedUntil = null;

    public function submit(): void
    {
        $this->validate();

        if ($this->honeypot !== '') {
            $this->isSent = true;

            return;
        }

        $key = 'contact-form:'.request()->ip();

        $executed = RateLimiter::attempt(
            key: $key,
            maxAttempts: 3,
            callback: static function (): void {},
            decaySeconds: 600,
        );

        if (! $executed) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = (int) ceil($seconds / 60);

            $this->rateLimitedUntil = $minutes === 1
                ? 'Tente novamente em 1 minuto.'
                : sprintf('Tente novamente em %s minutos.', $minutes);

            return;
        }

        $this->isLoading = true;

        try {
            $recipientEmail = config('services.contact.recipient_email', 'recrutamento@3pontos.com');

            Mail::mailer('resend')
                ->to($recipientEmail)
                ->send(new ContactFormMail(
                    senderName: $this->name,
                    senderEmail: $this->email,
                    senderPhone: $this->phone !== '' ? $this->phone : null,
                    messageBody: $this->message,
                    sentAt: now()->setTimezone('America/Sao_Paulo')->format('d/m/Y \à\s H:i:s'),
                ));

            $this->reset(['name', 'email', 'phone', 'message', 'honeypot']);
            $this->isSent = true;
        } catch (Throwable $throwable) {
            Log::error('ContactForm: failed to send email', [
                'error' => $throwable->getMessage(),
                'sender' => $this->email,
            ]);

            $this->hasError = true;
        } finally {
            $this->isLoading = false;
        }
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'email', 'phone', 'message', 'honeypot', 'isSent', 'hasError', 'rateLimitedUntil']);
        $this->resetValidation();
    }

    public function render(): Factory|View
    {
        return view('panel-app::livewire.contact-form');
    }
}
