<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Testing\TestAction;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Filament\Actions\ExportJobApplicationsAction;
use He4rt\Applications\Filament\Exports\ApplicationExporter;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\ListJobRequisitions;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\ViewJobRequisition;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

use function Pest\Laravel\actingAs;

function makeExportRecord(): Export
{
    return Export::query()->create([
        'exporter' => ApplicationExporter::class,
        'file_disk' => config('filament.default_filesystem_disk'),
        'file_name' => 'test-export',
        'processed_rows' => 0,
        'total_rows' => 0,
        'successful_rows' => 0,
        'user_id' => auth()->id(),
    ]);
}

beforeEach(function (): void {
    Bus::fake();
    Storage::fake(config('filament.default_filesystem_disk'));

    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    $this->recruiter = Recruiter::factory()->createOne();
    $this->recruiter->user->givePermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(Application::class));
    actingAs($this->recruiter->user);
    $this->team = $this->recruiter->team;
    $this->department = Department::factory()->forRecruiter($this->recruiter)->createOne();
    filament()->setTenant($this->team);

    $this->makeRequisition = function (array $attributes = []): JobRequisition {
        $requisition = JobRequisition::factory()
            ->for($this->team)
            ->for($this->department)
            ->for($this->recruiter, 'recruiter')
            ->for($this->recruiter->user, 'createdBy')
            ->create(['status' => RequisitionStatusEnum::Published, ...$attributes]);

        JobPosting::factory()->for($requisition, 'jobRequisition')->create();

        return $requisition->fresh();
    };
});

it('renders the export action in the view page header', function (): void {
    $requisition = ($this->makeRequisition)();

    Livewire::test(ViewJobRequisition::class, ['record' => $requisition->getKey()])
        ->assertActionVisible(TestAction::make(ExportJobApplicationsAction::class))
        ->assertActionEnabled(TestAction::make(ExportJobApplicationsAction::class))
        ->assertSee(__('applications::filament.export.action.label'));
});

it('exports only the applications of the requisition when triggered from the view page', function (): void {
    $requisition = ($this->makeRequisition)();
    $otherRequisition = ($this->makeRequisition)();

    Application::factory()->count(3)->for($this->team)->for($requisition, 'requisition')->create();
    Application::factory()->count(2)->for($this->team)->for($otherRequisition, 'requisition')->create();

    Livewire::test(ViewJobRequisition::class, ['record' => $requisition->getKey()])
        ->callAction(TestAction::make(ExportJobApplicationsAction::class))
        ->assertHasNoActionErrors();

    expect(Export::query()->latest('id')->first())
        ->not->toBeNull()
        ->total_rows->toBe(3);
});

it('exports only the applications of the requisition when triggered from the table', function (): void {
    $requisition = ($this->makeRequisition)();
    $otherRequisition = ($this->makeRequisition)();

    Application::factory()->count(4)->for($this->team)->for($requisition, 'requisition')->create();
    Application::factory()->count(5)->for($this->team)->for($otherRequisition, 'requisition')->create();

    Livewire::test(ListJobRequisitions::class)
        ->callAction(TestAction::make(ExportJobApplicationsAction::class)->table($requisition))
        ->assertHasNoActionErrors();

    expect(Export::query()->latest('id')->first())
        ->not->toBeNull()
        ->total_rows->toBe(4);
});

it('leaves out applications that belong to another team', function (): void {
    $requisition = ($this->makeRequisition)();

    Application::factory()->count(2)->for($this->team)->for($requisition, 'requisition')->create();

    $foreignApplication = Application::factory()->for($requisition, 'requisition')->create();
    expect($foreignApplication->team_id)->not->toBe($this->team->getKey());

    Livewire::test(ViewJobRequisition::class, ['record' => $requisition->getKey()])
        ->callAction(TestAction::make(ExportJobApplicationsAction::class))
        ->assertHasNoActionErrors();

    expect(Export::query()->latest('id')->first())->total_rows->toBe(2);
});

it('hides the action from users without permission to view applications', function (): void {
    $requisition = ($this->makeRequisition)();

    $this->recruiter->user->revokePermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(Application::class));
    resolve(PermissionRegistrar::class)->forgetCachedPermissions();

    Livewire::test(ViewJobRequisition::class, ['record' => $requisition->getKey()])
        ->assertActionHidden(TestAction::make(ExportJobApplicationsAction::class));
});

it('maps the candidate profile onto the exported row', function (): void {
    $requisition = ($this->makeRequisition)();

    $candidate = Candidate::factory()->create([
        'headline' => 'Senior Laravel Developer',
        'phone_number' => '+55 11 99999-0000',
    ]);

    $application = Application::factory()
        ->for($this->team)
        ->for($requisition, 'requisition')
        ->for($candidate)
        ->create(['tracking_code' => 'APP-0001-TEST']);

    $columnMap = [
        'tracking_code' => 'Tracking Code',
        'candidate.user.name' => 'Name',
        'candidate.user.email' => 'Email',
        'candidate.phone_number' => 'Phone',
        'candidate.headline' => 'Headline',
        'status' => 'Status',
    ];

    $exporter = new ApplicationExporter(
        export: makeExportRecord(),
        columnMap: $columnMap,
        options: [],
    );

    $row = $exporter($application->fresh(array_keys(ApplicationExporter::modifyQuery(Application::query())->getEagerLoads())));

    expect($row)->toBe([
        'APP-0001-TEST',
        $candidate->user->name,
        $candidate->user->email,
        "'+55 11 99999-0000",
        'Senior Laravel Developer',
        $application->status->getLabel(),
    ]);
});

it('formats dates for spreadsheet readers instead of dumping raw timestamps', function (): void {
    $requisition = ($this->makeRequisition)();

    $candidate = Candidate::factory()->create(['availability_date' => '2026-11-15']);

    $application = Application::factory()
        ->for($this->team)
        ->for($requisition, 'requisition')
        ->for($candidate)
        ->create(['created_at' => '2026-07-18 02:33:51']);

    $exporter = new ApplicationExporter(
        export: makeExportRecord(),
        columnMap: ['created_at' => 'Applied At', 'availability_date' => 'Available From'],
        options: [],
    );

    expect($exporter($application))->toBe(['18/07/2026 02:33', '15/11/2026']);
});

it('keeps the exported values in the language of whoever asked for the export', function (): void {
    $requisition = ($this->makeRequisition)();

    $application = Application::factory()
        ->for($this->team)
        ->for($requisition, 'requisition')
        ->create(['status' => ApplicationStatusEnum::InReview]);

    // Stands in for the worker, which boots with APP_LOCALE.
    app()->setLocale('en');

    $exporter = new ApplicationExporter(
        export: makeExportRecord(),
        columnMap: ['status' => 'Status'],
        options: ['locale' => 'pt_BR'],
    );

    expect($exporter($application))->toBe(['Em Revisão']);
});

it('carries the current locale into the export options', function (): void {
    app()->setLocale('pt_BR');

    expect(ExportJobApplicationsAction::make()->getOptions())->toBe(['locale' => 'pt_BR']);
});

it('escapes phone numbers that spreadsheets would read as a formula', function (): void {
    $requisition = ($this->makeRequisition)();

    $candidate = Candidate::factory()->create(['phone_number' => '+55 11 98765-4321']);

    $application = Application::factory()
        ->for($this->team)
        ->for($requisition, 'requisition')
        ->for($candidate)
        ->create();

    $exporter = new ApplicationExporter(
        export: makeExportRecord(),
        columnMap: ['candidate.phone_number' => 'Phone'],
        options: [],
    );

    expect($exporter($application))->toBe(["'+55 11 98765-4321"]);
});

it('escapes candidate text that spreadsheets would read as a formula', function (): void {
    $requisition = ($this->makeRequisition)();

    $candidate = Candidate::factory()->create(['headline' => '=HYPERLINK("http://evil.test")']);

    $application = Application::factory()
        ->for($this->team)
        ->for($requisition, 'requisition')
        ->for($candidate)
        ->create();

    $exporter = new ApplicationExporter(
        export: makeExportRecord(),
        columnMap: ['candidate.headline' => 'Headline'],
        options: [],
    );

    expect($exporter($application))->toBe(["'=HYPERLINK(\"http://evil.test\")"]);
});
