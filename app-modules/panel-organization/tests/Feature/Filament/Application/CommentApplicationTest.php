<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Models\Application;
use He4rt\Feedback\Actions\StoreCommentAction;
use He4rt\Feedback\DTOs\CommentDTO;
use He4rt\Feedback\Models\ApplicationComment;
use He4rt\Permissions\Permission;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    $this->evaluator = Recruiter::factory()->createOne();
    actingAs($this->evaluator->user);
    Permission::factory()
        ->create([
            'name' => 'view_applications',
            'guard_name' => 'web',
            'action' => PermissionsEnum::View,
        ]);
    Permission::factory()
        ->create([
            'name' => 'view_any_applications',
            'guard_name' => 'web',
            'action' => PermissionsEnum::View,
        ]);
    $this->evaluator->user->givePermissionTo('view_applications');
    $this->evaluator->user->givePermissionTo('view_any_applications');

    $this->application = Application::factory()->createOne();
    $requisition = $this->application->requisition;
    JobPosting::factory()->for($requisition)->create();
    filament()->setTenant($this->application->team);
});
it('should be able to comment at a application', function (): void {
    $sut = new StoreCommentAction;
    $sut->execute(
        CommentDTO::make([
            'team_id' => filament()->getTenant()->getKey(),
            'application_id' => $this->application->getKey(),
            'author_id' => auth()->user()->getKey(),
            'content' => 'comentario na quebrada foi a fuga que situação',
            'is_internal' => true,
        ])
    );

    assertDatabaseCount(ApplicationComment::class, 1);
    assertDatabaseHas(ApplicationComment::class, [
        'team_id' => filament()->getTenant()->getKey(),
        'application_id' => $this->application->getKey(),
        'author_id' => auth()->user()->getKey(),
        'content' => 'comentario na quebrada foi a fuga que situação',
        'is_internal' => true,
    ]);
});
