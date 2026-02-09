<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages;

use Filament\Resources\Pages\CreateRecord;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions\GenerateJobRequisitionAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\JobRequisitionResource;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class CreateJobRequisition extends CreateRecord
{
    public string $jobGenerationState = 'idle';

    protected static string $resource = JobRequisitionResource::class;

    public function getFooter(): ?View
    {
        return view('panel-organization::components.job-generation-overlay');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = filament()->getTenant();
        /** @phpstan-ignore-next-line  */
        $letter = $tenant->name[0];
        $data['slug'] = sprintf('%s-%s', $letter, Str::random(5));

        $data['team_id'] = $tenant->getKey();
        $data['created_by_id'] = auth()->id();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            GenerateJobRequisitionAction::make(),
        ];
    }
}
