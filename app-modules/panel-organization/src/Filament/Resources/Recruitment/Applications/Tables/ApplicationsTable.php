<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Models\Application;
use Illuminate\Database\Eloquent\Builder;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with([
                    'currentStage',
                    'requisition' => function ($q): void {
                        $q->with(['post', 'department', 'team']);
                    },
                    'requisition.stages' => fn ($q) => $q->orderBy('display_order'),
                    'stageHistory' => fn ($q) => $q->with('toStage')->latest()->limit(1),
                ])
            )
            ->columns([
                TextColumn::make('requisition.post.title')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('id')
                    ->label(__('applications::filament.fields.id'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                TextColumn::make('candidate.user.name')
                    ->label(__('applications::filament.fields.candidate'))
                    ->searchable()
                    ->sortable(),
                ViewColumn::make('pipeline_progress')
                    ->label(__('applications::filament.fields.pipeline_progress'))
                    ->view('panel-organization::filament.tables.columns.pipeline-progress')
                    ->sortable(false)
                    ->searchable(false),

                TextColumn::make('source')
                    ->label(__('applications::filament.fields.source'))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('offer_amount')
                    ->label(__('applications::filament.fields.offer_amount'))
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tracking_code')
                    ->label(__('applications::filament.fields.tracking_code'))
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                ViewColumn::make('last_movement')
                    ->label(__('panel-organization::filament.tables.last_movement'))
                    ->view('panel-organization::filament.tables.columns.last-movement')
                    ->sortable(false)
                    ->searchable(false),
                TextColumn::make('created_at')
                    ->label(__('applications::filament.fields.created_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('rejected_at')
                    ->label(__('applications::filament.fields.rejected_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('applications::filament.fields.updated_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('requisition.post.title')
            ->groups([
                Group::make('requisition_id')
                    ->label(__('panel-organization::filament.group.job'))
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn (Application $record): string => $record->requisition->post->title ?? __('panel-organization::filament.group.job_no_title'))
                    ->getDescriptionFromRecordUsing(function (Application $record): string {
                        $status = $record->requisition->status->getLabel();
                        $department = $record->requisition->department->name ?? 'N/A';
                        $team = $record->requisition->team->name ?? 'N/A';

                        return __('panel-organization::filament.group.job_description', ['team' => $team, 'department' => $department]);
                    }),
            ])
            ->selectable(false)
            ->groupingDirectionSettingHidden()
            ->defaultGroup('requisition_id')
            ->collapsedGroupsByDefault(false)
            ->filters([
                SelectFilter::make('status')
                    ->label(__('applications::filament.filters.status'))
                    ->options(ApplicationStatusEnum::class),
                SelectFilter::make('source')
                    ->label(__('applications::filament.filters.source'))
                    ->options(CandidateSourceEnum::class),
                SelectFilter::make('requisition')
                    ->label(__('applications::filament.filters.requisition'))
                    ->relationship('requisition', 'id', fn ($query) => $query->with('post'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->post->title ?? 'Vaga sem título')
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
