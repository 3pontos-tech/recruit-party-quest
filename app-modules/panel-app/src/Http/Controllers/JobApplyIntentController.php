<?php

declare(strict_types=1);

namespace He4rt\App\Http\Controllers;

use Filament\Notifications\Notification;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use Illuminate\Http\RedirectResponse;

final class JobApplyIntentController
{
    public function __invoke(string $record): RedirectResponse
    {
        $posting = JobPosting::query()
            ->where('slug', $record)
            ->with('jobRequisition')
            ->first();

        $isAvailable = $posting?->jobRequisition?->isPublished() ?? false;

        if (! $isAvailable) {
            Notification::make()
                ->title(__('panel-app::filament.pages.job_description.job_unavailable'))
                ->warning()
                ->send();

            return to_route('filament.app.resources.vagas.index');
        }

        return to_route('filament.app.resources.vagas.view', [
            'record' => $record,
            'apply' => 1,
        ]);
    }
}
