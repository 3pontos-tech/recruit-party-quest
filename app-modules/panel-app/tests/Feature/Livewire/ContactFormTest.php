<?php

declare(strict_types=1);

use He4rt\App\Livewire\ContactForm;
use He4rt\App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

use function Pest\Livewire\livewire;

it('renders the contact form', function (): void {
    livewire(ContactForm::class)
        ->assertOk()
        ->assertSee('Enviar mensagem')
        ->assertSet('isSent', false)
        ->assertSet('hasError', false);
});

it('sends the email and shows success state on valid submission', function (): void {
    Mail::fake();

    livewire(ContactForm::class)
        ->set('name', 'João Silva')
        ->set('email', 'joao@example.com')
        ->set('phone', '(11) 91234-5678')
        ->set('message', 'Gostaria de saber mais sobre as vagas disponíveis.')
        ->call('submit')
        ->assertSet('isSent', true)
        ->assertSet('hasError', false)
        ->assertSet('name', '')
        ->assertSet('email', '')
        ->assertSet('message', '');

    Mail::assertSent(ContactFormMail::class, fn (ContactFormMail $mail): bool => $mail->senderName === 'João Silva'
        && $mail->senderEmail === 'joao@example.com'
        && $mail->senderPhone === '(11) 91234-5678'
        && str_contains($mail->messageBody, 'saber mais sobre as vagas'));
});

it('sends the email without phone when phone is empty', function (): void {
    Mail::fake();

    livewire(ContactForm::class)
        ->set('name', 'Maria Souza')
        ->set('email', 'maria@example.com')
        ->set('phone', '')
        ->set('message', 'Tenho interesse em participar da comunidade.')
        ->call('submit')
        ->assertSet('isSent', true);

    Mail::assertSent(ContactFormMail::class, fn (ContactFormMail $mail): bool => $mail->senderPhone === 'Não informado');
});

it('fails validation when required fields are missing', function (): void {
    Mail::fake();

    livewire(ContactForm::class)
        ->call('submit')
        ->assertHasErrors(['name', 'email', 'message'])
        ->assertSet('isSent', false);

    Mail::assertNotSent(ContactFormMail::class);
});

it('fails validation with invalid email', function (): void {
    Mail::fake();

    livewire(ContactForm::class)
        ->set('name', 'Test User')
        ->set('email', 'not-an-email')
        ->set('message', 'Mensagem de teste qualquer aqui.')
        ->call('submit')
        ->assertHasErrors(['email'])
        ->assertSet('isSent', false);

    Mail::assertNotSent(ContactFormMail::class);
});

it('fails validation when message is too short', function (): void {
    Mail::fake();

    livewire(ContactForm::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('message', 'Curto')
        ->call('submit')
        ->assertHasErrors(['message'])
        ->assertSet('isSent', false);

    Mail::assertNotSent(ContactFormMail::class);
});

it('silently discards submission when honeypot is filled', function (): void {
    Mail::fake();

    livewire(ContactForm::class)
        ->set('name', 'Bot')
        ->set('email', 'bot@spam.com')
        ->set('message', 'Mensagem automática de spam enviada por bot.')
        ->set('honeypot', 'http://spam.example.com')
        ->call('submit')
        ->assertHasErrors(['honeypot']);

    Mail::assertNotSent(ContactFormMail::class);
});

it('shows success message after sending and can reset form', function (): void {
    Mail::fake();

    livewire(ContactForm::class)
        ->set('name', 'Reset Test')
        ->set('email', 'reset@example.com')
        ->set('message', 'Testando o reset do formulário após envio.')
        ->call('submit')
        ->assertSet('isSent', true)
        ->call('resetForm')
        ->assertSet('isSent', false)
        ->assertSet('hasError', false)
        ->assertSet('name', '')
        ->assertSet('email', '')
        ->assertSet('message', '');
});

it('sets hasError flag when mail sending fails', function (): void {
    Mail::shouldReceive('mailer->to->send')
        ->andThrow(new RuntimeException('Mail transport error'));

    livewire(ContactForm::class)
        ->set('name', 'Fail Test')
        ->set('email', 'fail@example.com')
        ->set('message', 'Mensagem que vai causar erro no envio.')
        ->call('submit')
        ->assertSet('isSent', false)
        ->assertSet('hasError', true);
});

it('blocks submissions after 3 attempts in 10 minutes', function (): void {
    Mail::fake();
    RateLimiter::clear('contact-form:127.0.0.1');

    $validPayload = [
        'name' => 'Teste Rate Limit',
        'email' => 'rate@example.com',
        'message' => 'Mensagem de teste para verificar o rate limit do formulário.',
    ];

    foreach (range(1, 3) as $attempt) {
        livewire(ContactForm::class)
            ->set('name', $validPayload['name'])
            ->set('email', $validPayload['email'])
            ->set('message', $validPayload['message'])
            ->call('submit')
            ->assertSet('isSent', true)
            ->assertSet('rateLimitedUntil', null)
            ->call('resetForm');
    }

    livewire(ContactForm::class)
        ->set('name', $validPayload['name'])
        ->set('email', $validPayload['email'])
        ->set('message', $validPayload['message'])
        ->call('submit')
        ->assertSet('isSent', false)
        ->assertSet('rateLimitedUntil', fn (mixed $value): bool => filled($value));

    Mail::assertSentCount(3);
});

it('clears rate limit message when resetForm is called', function (): void {
    Mail::fake();
    RateLimiter::clear('contact-form:127.0.0.1');

    $validPayload = [
        'name' => 'Teste Reset',
        'email' => 'reset-limit@example.com',
        'message' => 'Mensagem de teste para verificar o reset do rate limit.',
    ];

    foreach (range(1, 3) as $attempt) {
        livewire(ContactForm::class)
            ->set('name', $validPayload['name'])
            ->set('email', $validPayload['email'])
            ->set('message', $validPayload['message'])
            ->call('submit')
            ->assertSet('isSent', true)
            ->call('resetForm');
    }

    livewire(ContactForm::class)
        ->set('name', $validPayload['name'])
        ->set('email', $validPayload['email'])
        ->set('message', $validPayload['message'])
        ->call('submit')
        ->assertSet('rateLimitedUntil', fn (mixed $value): bool => filled($value))
        ->call('resetForm')
        ->assertSet('rateLimitedUntil', null)
        ->assertSet('isSent', false)
        ->assertSet('hasError', false);
});
