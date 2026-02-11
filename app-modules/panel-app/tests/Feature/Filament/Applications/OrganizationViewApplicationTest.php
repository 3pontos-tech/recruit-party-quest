<?php

declare(strict_types=1);

use He4rt\App\Filament\Resources\Applications\Pages\ViewApplication;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->candidate = Candidate::factory()->create();
    actingAs($this->candidate->user);
    $this->application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create();
    $this->candidate->user->givePermissionTo('view_applications');
});
it('should render', function (): void {
    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk();
});

test('only authorized user can see the application', function (): void {
    actingAs(User::factory()->createQuietly());
    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertforbidden();
});
test('should see only stages that are not hidden', function (): void {
    $hiddenStages = Stage::factory(2)->for($this->application->requisition, 'requisition')->create(['hidden' => true]);
    $visibleStages = Stage::factory(2)->for($this->application->requisition, 'requisition')->create(['active' => true, 'hidden' => false]);

    livewire(ViewApplication::class, ['record' => $this->application->id])
        ->assertOk()
        ->assertDontSee($hiddenStages->pluck('id')->toArray())
        ->assertSee($visibleStages->pluck('name')->toArray());
});
