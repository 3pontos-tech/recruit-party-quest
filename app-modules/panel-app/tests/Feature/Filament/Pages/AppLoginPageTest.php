<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Facades\Filament;
use He4rt\App\Filament\Pages\AppLoginPage;
use He4rt\Users\User;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::App->value);

    $this->user = User::factory()->create();
});

it('sends the user to the stored intent after logging in', function (): void {
    $intentUrl = url('/vagas/alguma-vaga/candidatar');
    session(['url.intended' => $intentUrl]);

    livewire(AppLoginPage::class)
        ->fillForm([
            'email' => $this->user->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertRedirect($intentUrl);
});

it('falls back to the panel home when no intent is stored', function (): void {
    livewire(AppLoginPage::class)
        ->fillForm([
            'email' => $this->user->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertRedirect(Filament::getUrl());
});
