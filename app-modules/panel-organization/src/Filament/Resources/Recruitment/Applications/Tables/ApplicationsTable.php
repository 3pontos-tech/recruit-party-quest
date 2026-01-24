<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Tables;

use Filament\Actions\EditAction;
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
                        $q->with(['post', 'department', 'team'])
                            ->withCount('applications');
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
                    ->label('Progresso no Pipeline')
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
                    ->label('Última Movimentação')
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
                Group::make('requisition')
                    ->label('Vaga')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn (Application $record): string => $record->requisition->post->title ?? 'Vaga sem título')
                    ->getDescriptionFromRecordUsing(function (Application $record): string {
                        $status = $record->requisition->status->getLabel();
                        $department = $record->requisition->department->name ?? 'N/A';
                        $team = $record->requisition->team->name ?? 'N/A';

                        return sprintf('Empresa: %s • Departamento: %s • Status: %s', $team, $department, $status);
                    }),
            ])
            ->selectable(false)
            ->defaultGroup('requisition')
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
                EditAction::make(),
            ]);
    }
}
