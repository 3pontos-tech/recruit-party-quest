<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
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
                TextColumn::make('id')
                    ->label(__('recruitment::filament.requisition.fields.id'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                TextColumn::make('team.name')
                    ->label(__('recruitment::filament.requisition.fields.team'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label(__('recruitment::filament.requisition.fields.department'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('recruitment::filament.requisition.fields.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('priority')
                    ->label(__('recruitment::filament.requisition.fields.priority'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('work_arrangement')
                    ->label(__('recruitment::filament.requisition.fields.work_arrangement'))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('employment_type')
                    ->label(__('recruitment::filament.requisition.fields.employment_type'))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('experience_level')
                    ->label(__('recruitment::filament.requisition.fields.experience_level'))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('positions_available')
                    ->label(__('recruitment::filament.requisition.fields.positions_available'))
                    ->sortable(),
                TextColumn::make('salary_range')
                    ->label(__('recruitment::filament.requisition.fields.salary_range'))
                    ->state(fn (JobRequisition $record): string => self::formatSalaryRange($record))
                    ->toggleable(),
                TextColumn::make('hiringManager.name')
                    ->label(__('recruitment::filament.requisition.fields.hiring_manager'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label(__('recruitment::filament.requisition.fields.published_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('recruitment::filament.requisition.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('recruitment::filament.requisition.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('recruitment::filament.requisition.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
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
