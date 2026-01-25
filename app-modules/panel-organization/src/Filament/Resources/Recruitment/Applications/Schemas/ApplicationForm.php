<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('applications::filament.sections.application_info'))
                    ->columns(2)
                    ->schema([
                        Select::make('team_id')
                            ->relationship('team', 'name')
                            ->label(__('teams::filament.department.fields.team'))
                            ->live(),

                        Select::make('requisition_id')
                            ->label(__('applications::filament.fields.requisition'))
                            ->relationship(
                                name: 'requisition',
                                titleAttribute: 'id'
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->post->title ?? $record->id)
                            ->required()
                            ->preload()
                            ->searchable()
                            ->live(),
                        Select::make('candidate_id')
                            ->label(__('applications::filament.fields.candidate'))
                            ->relationship(
                                name: 'candidate',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn ($query) => $query->with('user:id,name')->select(['id', 'user_id']),
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name ?? $record->id)
                            ->required()
                            ->preload()
                            ->searchable(),
                        Select::make('status')
                            ->label(__('applications::filament.fields.status'))
                            ->options(ApplicationStatusEnum::class)
                            ->default(ApplicationStatusEnum::New)
                            ->required()
                            ->live(),
                        Select::make('source')
                            ->label(__('applications::filament.fields.source'))
                            ->options(CandidateSourceEnum::class)
                            ->required(),
                        TextInput::make('source_details')
                            ->label(__('applications::filament.fields.source_details'))
                            ->maxLength(255),
                        Select::make('current_stage_id')
                            ->label(__('applications::filament.fields.current_stage'))
                            ->relationship(
                                name: 'currentStage',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query, $get) => $query
                                    ->where('job_requisition_id', $get('requisition_id'))
                                    ->orderBy('display_order')
                            )
                            ->preload()
                            ->searchable(),
                    ]),

                Section::make(__('applications::filament.sections.cover_letter'))
                    ->schema([
                        RichEditor::make('cover_letter')
                            ->label(__('applications::filament.fields.cover_letter'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('applications::filament.sections.rejection'))
                    ->columns(2)
                    ->visible(fn ($get): bool => $get('status') === ApplicationStatusEnum::Rejected->value)
                    ->schema([
                        DateTimePicker::make('rejected_at')
                            ->label(__('applications::filament.fields.rejected_at')),
                        Select::make('rejected_by')
                            ->label(__('applications::filament.fields.rejected_by'))
                            ->relationship(
                                name: 'rejectedBy',
                                titleAttribute: 'name',
                                modifyQueryUsing: function ($query, $get) {
                                    $teamId = $get('team_id');

                                    if ($teamId) {
                                        return $query->whereHas('teams', fn ($q) => $q->whereKey($teamId));
                                    }

                                    return $query;
                                },
                            )
                            ->preload()
                            ->searchable(),
                        Select::make('rejection_reason_category')
                            ->label(__('applications::filament.fields.rejection_reason_category'))
                            ->options(RejectionReasonCategoryEnum::class),
                        Textarea::make('rejection_reason_details')
                            ->label(__('applications::filament.fields.rejection_reason_details'))
                            ->rows(3),
                    ]),

                Section::make(__('applications::filament.sections.offer'))
                    ->columns(2)
                    ->visible(fn ($get): bool => in_array($get('status'), [
                        ApplicationStatusEnum::OfferExtended->value,
                        ApplicationStatusEnum::OfferAccepted->value,
                        ApplicationStatusEnum::OfferDeclined->value,
                    ]))
                    ->schema([
                        DateTimePicker::make('offer_extended_at')
                            ->label(__('applications::filament.fields.offer_extended_at')),
                        Select::make('offer_extended_by')
                            ->label(__('applications::filament.fields.offer_extended_by'))
                            ->relationship(
                                name: 'offerExtendedBy',
                                titleAttribute: 'name',
                                modifyQueryUsing: function ($query, $get) {
                                    $teamId = $get('team_id');

                                    if ($teamId) {
                                        return $query->whereHas('teams', fn ($q) => $q->whereKey($teamId));
                                    }

                                    return $query;
                                },
                            )
                            ->preload()
                            ->searchable(),
                        TextInput::make('offer_amount')
                            ->label(__('applications::filament.fields.offer_amount'))
                            ->numeric()
                            ->prefix('$'),
                        DateTimePicker::make('offer_response_deadline')
                            ->label(__('applications::filament.fields.offer_response_deadline')),
                    ]),
            ]);
    }
}
