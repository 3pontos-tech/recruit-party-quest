<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions;

use App\Enums\FilamentPanel;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource as CandidateJobRequisitionResource;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

class CopyJobShareLinkAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('panel-organization::filament.actions.copy_share_link.label'))
            ->icon(Heroicon::OutlinedLink)
            ->color('gray')
            ->authorize('view')
            ->disabled(fn (JobRequisition $record): bool => blank(self::shareUrlFor($record)))
            ->tooltip(fn (JobRequisition $record): ?string => blank(self::shareUrlFor($record))
                ? __('panel-organization::filament.actions.copy_share_link.tooltip_unavailable')
                : null)
            ->actionJs(fn (JobRequisition $record): string => self::clipboardJs($record));
    }

    public static function shareUrlFor(JobRequisition $record): ?string
    {
        $slug = $record->post?->slug;

        if (blank($slug)) {
            return null;
        }

        return CandidateJobRequisitionResource::getUrl(
            name: 'view',
            parameters: ['record' => $slug],
            panel: FilamentPanel::App->value,
        );
    }

    public static function getDefaultName(): ?string
    {
        return 'copyShareLink';
    }

    private static function clipboardJs(JobRequisition $record): string
    {
        $url = json_encode(self::shareUrlFor($record), JSON_THROW_ON_ERROR);
        $message = json_encode(
            __('panel-organization::filament.actions.copy_share_link.notification_copied'),
            JSON_THROW_ON_ERROR,
        );

        return <<<JS
            if ({$url}) {
                window.navigator.clipboard.writeText({$url});
                new FilamentNotification().title({$message}).success().send();
            }
            JS;
    }
}
