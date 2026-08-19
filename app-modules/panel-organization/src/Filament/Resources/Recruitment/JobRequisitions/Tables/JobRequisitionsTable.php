<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use He4rt\Applications\Filament\Actions\ExportJobApplicationsAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions\CopyJobShareLinkAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions\DuplicateJobRequisitionAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions\DuplicateJobRequisitionBulkAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\JobRequisitionResource;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

class JobRequisitionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post.title')
                    ->label(__('panel-organization::filament.tables.position'))
                    ->weight(FontWeight::SemiBold)
                    ->searchable()
                    ->sortable()
                    ->description(fn (JobRequisition $record) => $record->team->name.' · '.$record->department->name),
                TextColumn::make('status')
                    ->label(__('recruitment::filament.requisition.fields.status'))
                    ->badge()
                    ->alignCenter(),
                TextColumn::make('priority')
                    ->label(__('recruitment::filament.requisition.fields.priority'))
                    ->badge()
                    ->alignCenter(),
                TextColumn::make('work_arrangement')
                    ->label(__('recruitment::filament.requisition.fields.work_arrangement'))
                    ->badge(),
                TextColumn::make('employment_type')
                    ->label(__('recruitment::filament.requisition.fields.employment_type'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size(TextSize::Small)
                    ->placeholder(__('recruitment::filament.requisition.fields.not_specified'))
                    ->color('gray'),
                TextColumn::make('work_schedule')
                    ->label(__('recruitment::filament.requisition.fields.work_schedule'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size(TextSize::Small)
                    ->placeholder(__('recruitment::filament.requisition.fields.not_specified'))
                    ->color('gray'),
                TextColumn::make('experience_level')
                    ->label(__('recruitment::filament.requisition.fields.experience_level'))
                    ->badge()
                    ->alignCenter(),
                TextColumn::make('positions_available')
                    ->label(__('recruitment::filament.requisition.fields.positions_available'))
                    ->alignCenter(),
                TextColumn::make('salary')
                    ->label(__('recruitment::filament.requisition.fields.salary_range'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->state(fn (JobRequisition $record): string => self::formatSalaryRange($record))
                    ->icon(fn ($record) => $record->is_confidential ? Heroicon::LockClosed : null
                    ),
                TextColumn::make('recruiter.user.name')
                    ->label(__('recruitment::filament.requisition.fields.recruiter'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('published_at')
                    ->label(__('recruitment::filament.requisition.fields.published_at'))
                    ->date(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('recruitment::filament.requisition.filters.status'))
                    ->options(RequisitionStatusEnum::class),
                SelectFilter::make('priority')
                    ->label(__('recruitment::filament.requisition.filters.priority'))
                    ->options(RequisitionPriorityEnum::class),
                SelectFilter::make('work_arrangement')
                    ->label(__('recruitment::filament.requisition.filters.work_arrangement'))
                    ->options(WorkArrangementEnum::class),
                SelectFilter::make('employment_type')
                    ->label(__('recruitment::filament.requisition.filters.employment_type'))
                    ->options(EmploymentTypeEnum::class),
                SelectFilter::make('experience_level')
                    ->label(__('recruitment::filament.requisition.filters.experience_level'))
                    ->options(ExperienceLevelEnum::class),
                SelectFilter::make('team')
                    ->label(__('recruitment::filament.requisition.filters.team'))
                    ->relationship('team', 'name')
                    ->preload(),
                SelectFilter::make('department')
                    ->label(__('recruitment::filament.requisition.filters.department'))
                    ->relationship('department', 'name')
                    ->preload(),
                TernaryFilter::make('is_internal_only')
                    ->label(__('recruitment::filament.requisition.filters.is_internal_only')),
                TernaryFilter::make('is_confidential')
                    ->label(__('recruitment::filament.requisition.filters.is_confidential')),
                TrashedFilter::make(),
            ])
            ->recordActions([
                CopyJobShareLinkAction::make()->tableIconButton(),
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('kanban')
                        ->label(__('panel-organization::filament.tables.kanban'))
                        ->icon(Heroicon::OutlinedViewColumns)
                        ->url(fn (JobRequisition $record): string => JobRequisitionResource::getUrl('kanban',
                            ['record' => $record->id])),
                    ExportJobApplicationsAction::make(),
                    DuplicateJobRequisitionAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DuplicateJobRequisitionBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ])->visible(fn (): bool => auth()->user()->hasAnyRole([Roles::Admin, Roles::SuperAdmin])),
            ]);
    }

    private static function formatSalaryRange(JobRequisition $record): string
    {
        if (! $record->salary_range_min || ! $record->salary_range_max) {
            return '-';
        }

        return sprintf(
            '%s - %s %s',
            number_format($record->salary_range_min),
            number_format($record->salary_range_max),
            $record->salary_currency
        );
    }
}
