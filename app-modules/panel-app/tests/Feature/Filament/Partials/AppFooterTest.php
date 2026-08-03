<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Filament\Pages\LandingPage;
use He4rt\Term\Filament\Pages\TermPage;
use He4rt\Term\Models\Term;

use function Pest\Laravel\get;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::App->value);
});

it('renders footer term links from the given prop without querying the database', function (): void {
    $this->blade(
        '<x-he4rt::partials.footer :terms="$terms" />',
        ['terms' => [
            ['title' => 'Custom Term', 'url' => 'https://example.test/custom-term'],
        ]],
    )
        ->assertSee('Custom Term')
        ->assertSee('https://example.test/custom-term', false);
});

it('lists active terms in the landing page footer using the panel-aware url', function (): void {
    $active = Term::factory()->create(['title' => 'Privacy Policy', 'slug' => 'privacy-policy']);
    Term::factory()->inactive()->create(['title' => 'Retired Policy', 'slug' => 'retired-policy']);

    get(LandingPage::getUrl())
        ->assertOk()
        ->assertSee('Privacy Policy')
        ->assertSee(TermPage::getUrl(['slug' => $active->slug]), false)
        ->assertDontSee('Retired Policy');
});
