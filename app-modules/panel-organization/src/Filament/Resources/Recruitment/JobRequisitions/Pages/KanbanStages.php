<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\JobRequisitionResource;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use Livewire\Attributes\Locked;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardResourcePage;
use Relaticle\Flowforge\Column;
use Relaticle\Flowforge\Concerns\InteractsWithBoard;

class KanbanStages extends BoardResourcePage
{
    use InteractsWithBoard;

    #[Locked]
    public ?string $requisitionId = null;

    protected static string $resource = JobRequisitionResource::class;

    public function mount(): void
    {
        $this->requisitionId = request()->route('record');

        filament()
            ->getCurrentPanel()
            ->sidebarCollapsibleOnDesktop();
    }

    public function rendered(): void
    {
        $this->js('$store.sidebar.close()');
    }

    public function board(Board $board): Board
    {
        // 1. Columns precisam de identificação única. (usar stage->getKey())
        //
        // 2. Applications with some validations.

        $jobRequisition = JobRequisition::query()
            ->with([
                'stages',
                'post',
            ])
            ->findOrFail($this->requisitionId);

        $columns = collect($jobRequisition->stages)
            ->map(fn (Stage $stage) => Column::make($stage->id)
                ->label(sprintf('%s (%s)', $stage->name, $stage->stage_type->getLabel()))
                ->color($stage->stage_type->getColor())
            )->toArray();

        return $board
            ->recordTitleAttribute('candidate.user.name')
            ->cardSchema(fn (Schema $schema) => $schema
                ->components([
                    TextEntry::make('status')->badge(),
                    TextEntry::make('candidate.total_work_experience_years'),
                    TextEntry::make('tracking_code'),
                ])
            )
            ->cardActions([
                Action::make('process-placement')
                    ->outlined()
                    ->label('Processar Aporte')
                    ->icon('heroicon-o-play')
                    ->disabled(fn (Application $record): bool => ! $record->current_step->canChange())
                    ->tooltip(fn (Application $record): ?string => $record->current_step->canChange() ? null : 'O processo atual não permite mudanças.')
                    ->schema(function (Application $record): array {
                        $choices = $record->current_step->choices();

                        return [
                            Select::make('to_status')
                                ->label('Novo Status')
                                ->options($choices)
                                ->enum(ApplicationStatusEnum::class)
                                ->required()
                                ->live(),

                            Select::make('to_stage_id')
                                ->label('Estágio')
                                ->options(fn () => $record->requisition->stages
                                    ->where('active', true)
                                    ->pluck('name', 'id'))
                                ->visible(fn (Get $get) => $get('to_status') === ApplicationStatusEnum::InProgress
                                    || $get('to_status') === ApplicationStatusEnum::InReview
                                )
                                ->helperText('Selecione um estágio específico ou deixe vazio para avançar automaticamente'),

                            Textarea::make('rejection_reason_details')
                                ->label('Motivo da Recusa')
                                ->rows(3)
                                ->visible(fn (Get $get) => $get('to_status') === ApplicationStatusEnum::Rejected
                                )
                                ->required(fn (Get $get) => $get('to_status') === ApplicationStatusEnum::Rejected),

                            Textarea::make('notes')
                                ->label('Notas')
                                ->rows(2),
                        ];
                    })
                    ->action(function (Application $record, array $data): void {
                        $transitionData = TransitionData::fromArray($data, auth()->id());
                        $record->current_step->handle($transitionData);
                    })
                    ->requiresConfirmation(),
                EditAction::make()
                    ->model(Application::class),
            ])
            ->query(
                Application::query()
                    ->where('requisition_id', $jobRequisition->getKey())
            )
            ->columnIdentifier('current_stage_id')
            ->columns($columns);
    }
}
