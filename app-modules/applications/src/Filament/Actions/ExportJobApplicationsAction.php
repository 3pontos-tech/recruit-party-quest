<?php

declare(strict_types=1);

namespace He4rt\Applications\Filament\Actions;

use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Support\Icons\Heroicon;
use He4rt\Applications\Filament\Exports\ApplicationExporter;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class ExportJobApplicationsAction extends ExportAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('applications::filament.export.action.label'))
            ->icon(Heroicon::OutlinedTableCells)
            ->modalHeading(__('applications::filament.export.action.modal_heading'))
            ->modalDescription(__('applications::filament.export.action.modal_description'))
            ->exporter(ApplicationExporter::class)
            ->columnMappingColumns(2)
            ->options(fn (): array => ['locale' => App::getLocale()])
            ->visible(fn (JobRequisition $record): bool => auth()->user()?->can('viewAny', Application::class) ?? false)
            // Rebuilt from scratch: inside a table Filament hands over the JobRequisition query.
            ->modifyQueryUsing(fn (JobRequisition $record): Builder => ApplicationExporter::modifyQuery(
                Application::query()
                    ->where('requisition_id', $record->getKey())
                    ->where('team_id', $record->team_id)
            ))
            ->fileName(fn (Export $export, JobRequisition $record): string => sprintf(
                '%s-%s-%d',
                Str::slug(__('applications::filament.export.action.file_name')),
                Str::slug($record->postTitle() ?? $record->getKey()),
                $export->getKey(),
            ));
    }

    public static function getDefaultName(): ?string
    {
        return 'exportApplications';
    }
}
