<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;

it('enables database notifications on the candidate (app) panel so the bell renders', function (): void {
    expect(filament()->getPanel(FilamentPanel::App->value)->hasDatabaseNotifications())->toBeTrue();
});
