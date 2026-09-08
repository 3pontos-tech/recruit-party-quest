<?php

declare(strict_types=1);

use He4rt\Applications\Actions\MarkApplicationAsViewed;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationView;
use He4rt\Users\User;

use function Pest\Laravel\assertDatabaseCount;

it('records the first view with the viewer and the application team', function (): void {
    $application = Application::factory()->create();
    $viewer = User::factory()->create();

    $view = resolve(MarkApplicationAsViewed::class)->execute($application, $viewer);

    expect($view)->toBeInstanceOf(ApplicationView::class)
        ->and($view->application_id)->toBe($application->getKey())
        ->and($view->team_id)->toBe($application->team_id)
        ->and($view->viewed_by)->toBe($viewer->getKey())
        ->and($view->viewed_at)->not->toBeNull();

    assertDatabaseCount(ApplicationView::class, 1);
});

it('is idempotent and keeps the first viewer', function (): void {
    $application = Application::factory()->create();
    $first = User::factory()->create();
    $second = User::factory()->create();

    resolve(MarkApplicationAsViewed::class)->execute($application, $first);
    $view = resolve(MarkApplicationAsViewed::class)->execute($application, $second);

    assertDatabaseCount(ApplicationView::class, 1);
    expect($view->viewed_by)->toBe($first->getKey());
});

it('exposes seen state through the relation and the scopes', function (): void {
    $seen = Application::factory()->create();
    $unseen = Application::factory()->create();
    resolve(MarkApplicationAsViewed::class)->execute($seen, User::factory()->create());

    expect($seen->fresh()->isSeenByTeam())->toBeTrue()
        ->and($unseen->fresh()->isSeenByTeam())->toBeFalse()
        ->and(Application::query()->seenByTeam()->pluck('id')->all())->toBe([$seen->getKey()])
        ->and(Application::query()->unseenByTeam()->pluck('id')->all())->toBe([$unseen->getKey()]);
});
