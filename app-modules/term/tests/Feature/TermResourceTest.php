<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use He4rt\Permissions\Roles;
use He4rt\Term\Filament\Resources\Pages\CreateTerm;
use He4rt\Term\Filament\Resources\Pages\EditTerm;
use He4rt\Term\Filament\Resources\Pages\ListTerms;
use He4rt\Term\Filament\Resources\TermResource;
use He4rt\Term\Models\Term;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    actingAs(User::factory()->create());

    artisan('sync:permissions');

    auth()->user()->assignRole(Roles::SuperAdmin->value);
});

it('can list terms', function (): void {
    $terms = Term::factory()->count(3)->create();

    livewire(ListTerms::class)
        ->assertCanSeeTableRecords($terms)
        ->assertSuccessful();
});

it('can render create page', function (): void {
    $this->get(TermResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create a term', function (): void {
    livewire(CreateTerm::class)
        ->fillForm([
            'title' => 'Terms of Service',
            'slug' => 'terms-of-service',
            'is_active' => true,
            'content' => [
                [
                    'title' => 'Introduction',
                    'id' => 'introduction',
                    'show_in_sidebar' => true,
                    'body' => '<p>Welcome to our terms.</p>',
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Term::query()->where('slug', 'terms-of-service')->exists())->toBeTrue();
});

it('can render edit page', function (): void {
    $term = Term::factory()->create();

    $this->get(TermResource::getUrl('edit', ['record' => $term]))
        ->assertSuccessful();
});

it('can edit a term', function (): void {
    $term = Term::factory()->create();

    livewire(EditTerm::class, ['record' => $term->getRouteKey()])
        ->fillForm([
            'title' => 'Updated Title',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $term->refresh();
    expect($term->title)->toBe('Updated Title');
});

it('validates required fields on create', function (): void {
    livewire(CreateTerm::class)
        ->fillForm([
            'title' => null,
            'slug' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'title' => 'required',
            'slug' => 'required',
        ]);
});

it('validates unique slug', function (): void {
    Term::factory()->create(['slug' => 'existing-slug']);

    livewire(CreateTerm::class)
        ->fillForm([
            'title' => 'Test',
            'slug' => 'existing-slug',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

it('can search terms by title', function (): void {
    $terms = Term::factory()->count(3)->create();
    $searchTerm = $terms->first();

    livewire(ListTerms::class)
        ->searchTable($searchTerm->title)
        ->assertCanSeeTableRecords($terms->filter(fn ($t) => $t->id === $searchTerm->id))
        ->assertCanNotSeeTableRecords($terms->filter(fn ($t) => $t->id !== $searchTerm->id));
});
