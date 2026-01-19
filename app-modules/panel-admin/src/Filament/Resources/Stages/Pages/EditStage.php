<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Stages\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use He4rt\Admin\Filament\Resources\Recruitment\JobRequisitions\JobRequisitionResource;
use He4rt\Admin\Filament\Resources\Stages\StageResource;
use He4rt\Recruitment\Stages\Models\Stage;

/**
 * @method Stage getRecord()
 */
class EditStage extends EditRecord
{
    protected static string $resource = StageResource::class;

    public function getBreadcrumbs(): array
    {
        $stage = $this->getRecord();

        return [
            config('app.url').'/admin' => 'Dashboard',
            JobRequisitionResource::getUrl('edit', ['record' => $stage->job_requisition_id]) => 'Vaga: '.$stage->requisition->post->title ?? 'Editar Vaga',
            $stage->name,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
