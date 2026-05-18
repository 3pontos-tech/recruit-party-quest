<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JobRequisitionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make(__('recruitment::filament.requisition.sections.basic_information'))
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('post.title')
                            ->label(__('recruitment::filament.requisition.job_posting.fields.title'))
                            ->columnSpanFull(),
                        TextEntry::make('status')
                            ->label(__('recruitment::filament.requisition.fields.status'))
                            ->badge(),
                        TextEntry::make('priority')
                            ->label(__('recruitment::filament.requisition.fields.priority'))
                            ->badge(),
                        TextEntry::make('post.summary')
                            ->label(__('recruitment::filament.requisition.job_posting.fields.summary'))
                            ->columnSpanFull(),
                        TextEntry::make('post.description')
                            ->label(__('recruitment::filament.requisition.job_posting.fields.description'))
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Section::make(__('recruitment::filament.requisition.sections.position_details'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('department.name')
                            ->label(__('recruitment::filament.requisition.fields.department')),
                        TextEntry::make('work_arrangement')
                            ->label(__('recruitment::filament.requisition.fields.work_arrangement'))
                            ->badge(),
                        TextEntry::make('employment_type')
                            ->label(__('recruitment::filament.requisition.fields.employment_type'))
                            ->badge()
                            ->placeholder(__('recruitment::filament.requisition.fields.not_specified')),
                        TextEntry::make('work_schedule')
                            ->label(__('recruitment::filament.requisition.fields.work_schedule'))
                            ->badge()
                            ->placeholder(__('recruitment::filament.requisition.fields.not_specified')),
                        TextEntry::make('experience_level')
                            ->label(__('recruitment::filament.requisition.fields.experience_level'))
                            ->badge(),
                        TextEntry::make('category')
                            ->label(__('recruitment::filament.requisition.fields.category'))
                            ->badge(),
                        TextEntry::make('positions_available')
                            ->label(__('recruitment::filament.requisition.fields.positions_available')),
                    ]),

                Section::make(__('recruitment::filament.requisition.sections.team_ownership'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('recruiter.user.name')
                            ->label(__('recruitment::filament.requisition.fields.hiring_manager')),
                    ]),

                Section::make(__('recruitment::filament.requisition.sections.compensation'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('salary_range_min')
                            ->label(__('recruitment::filament.requisition.fields.salary_range_min'))
                            ->prefix('$'),
                        TextEntry::make('salary_range_max')
                            ->label(__('recruitment::filament.requisition.fields.salary_range_max'))
                            ->prefix('$'),
                        TextEntry::make('salary_currency')
                            ->label(__('recruitment::filament.requisition.fields.salary_currency')),
                        IconEntry::make('show_salary_to_candidates')
                            ->label(__('recruitment::filament.requisition.fields.show_salary_to_candidates'))
                            ->boolean(),
                    ]),

                Section::make(__('recruitment::filament.requisition.sections.settings'))
                    ->columns(3)
                    ->schema([
                        IconEntry::make('is_internal_only')
                            ->label(__('recruitment::filament.requisition.fields.is_internal_only'))
                            ->boolean(),
                        IconEntry::make('is_confidential')
                            ->label(__('recruitment::filament.requisition.fields.is_confidential'))
                            ->boolean(),
                        TextEntry::make('target_start_at')
                            ->label(__('recruitment::filament.requisition.fields.target_start_at'))
                            ->dateTime(),
                    ]),
            ]);
    }
}
