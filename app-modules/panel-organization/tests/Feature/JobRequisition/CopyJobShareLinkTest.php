<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Actions\Testing\TestAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions\CopyJobShareLinkAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\EditJobRequisition;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\ListJobRequisitions;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\ViewJobRequisition;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * Extrai o valor do atributo `x-on:click` — já parseado pelas regras de atributo HTML,
 * como o navegador faria — do primeiro elemento cujo handler contenha $needle.
 * Retorna null quando nenhum handler intacto contém o trecho (ex.: atributo truncado
 * por escaping inválido de aspas).
 */
function clickHandlerContaining(string $html, string $needle): ?string
{
    $dom = new DOMDocument();
    @$dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>', LIBXML_NOERROR | LIBXML_NOWARNING);

    foreach ($dom->getElementsByTagName('*') as $node) {
        $handler = $node->getAttribute('x-on:click');

        if ($handler !== '' && str_contains($handler, $needle)) {
            return $handler;
        }
    }

    return null;
}

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    $this->recruiter = Recruiter::factory()->createOne();
    actingAs($this->recruiter->user);
    $this->team = $this->recruiter->team;
    $this->department = Department::factory()->forRecruiter($this->recruiter)->createOne();
    filament()->setTenant($this->team);

    // Default Published: a action só faz sentido para vagas publicadas, então o happy
    // path parte daí. Testes que precisam de outro status sobrescrevem via $attributes.
    $this->makeRequisition = fn (array $attributes = []): JobRequisition => JobRequisition::factory()
        ->for($this->team)
        ->for($this->department)
        ->for($this->recruiter, 'recruiter')
        ->for($this->recruiter->user, 'createdBy')
        ->create(['status' => RequisitionStatusEnum::Published, ...$attributes]);
});

it('builds the candidate detail URL from the job posting slug', function (): void {
    $requisition = ($this->makeRequisition)();
    $posting = JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    $url = CopyJobShareLinkAction::shareUrlFor($requisition->fresh());

    expect($url)
        ->toBeString()
        ->toContain('/vagas/'.$posting->slug)
        ->toStartWith('http');
});

it('returns null when the requisition has no job posting', function (): void {
    $requisition = ($this->makeRequisition)();

    expect(CopyJobShareLinkAction::shareUrlFor($requisition->fresh()))->toBeNull();
});

it('returns null when the requisition is not published', function (): void {
    $requisition = ($this->makeRequisition)(['status' => RequisitionStatusEnum::Draft]);
    JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    expect(CopyJobShareLinkAction::shareUrlFor($requisition->fresh()))->toBeNull();
});

it('shows the copy share link action enabled when the job has a posting', function (): void {
    $requisition = ($this->makeRequisition)();
    JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    Livewire::test(ListJobRequisitions::class)
        ->assertActionEnabled(TestAction::make('copyShareLink')->table($requisition));
});

it('keeps the copy share link action enabled for internal jobs that have a posting', function (): void {
    $requisition = ($this->makeRequisition)(['is_internal_only' => true]);
    JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    Livewire::test(ListJobRequisitions::class)
        ->assertActionEnabled(TestAction::make('copyShareLink')->table($requisition));
});

it('disables the copy share link action when the job has no posting', function (): void {
    $requisition = ($this->makeRequisition)();

    Livewire::test(ListJobRequisitions::class)
        ->assertActionDisabled(TestAction::make('copyShareLink')->table($requisition));
});

it('disables the copy share link action when the job is not published', function (): void {
    $requisition = ($this->makeRequisition)(['status' => RequisitionStatusEnum::Draft]);
    JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    Livewire::test(ListJobRequisitions::class)
        ->assertActionDisabled(TestAction::make('copyShareLink')->table($requisition));
});

it('shows the copy share link action in the view page header', function (): void {
    $requisition = ($this->makeRequisition)();
    JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    Livewire::test(ViewJobRequisition::class, ['record' => $requisition->getKey()])
        ->assertActionExists('copyShareLink')
        ->assertActionEnabled('copyShareLink');
});

it('shows the copy share link action in the edit page header', function (): void {
    $this->recruiter->user->givePermissionTo('update_job_requisitions');

    $requisition = ($this->makeRequisition)();
    JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    Livewire::test(EditJobRequisition::class, ['record' => $requisition->getKey()])
        ->assertActionExists('copyShareLink')
        ->assertActionEnabled('copyShareLink');
});

it('renders a clipboard handler that survives HTML attribute encoding intact', function (): void {
    $requisition = ($this->makeRequisition)();
    $posting = JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    $html = Livewire::test(ViewJobRequisition::class, ['record' => $requisition->getKey()])->html();

    $handler = clickHandlerContaining($html, 'navigator.clipboard.writeText');

    expect($handler)->not->toBeNull();
    expect($handler)
        ->toContain('navigator.clipboard.writeText')
        ->toContain('FilamentNotification')
        ->toContain($posting->slug);
});
