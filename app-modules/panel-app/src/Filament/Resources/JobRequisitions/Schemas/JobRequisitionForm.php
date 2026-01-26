<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Resources\JobRequisitions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;

class JobRequisitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('team_id')
                    ->relationship('team', 'name')
                    ->required(),
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->required(),
                Select::make('work_arrangement')
                    ->options(WorkArrangementEnum::class)
                    ->required(),
                Select::make('employment_type')
                    ->options(EmploymentTypeEnum::class)
                    ->required(),
                Select::make('experience_level')
                    ->options(ExperienceLevelEnum::class)
                    ->required(),
                TextInput::make('positions_available')
                    ->required(),
                TextInput::make('salary_range_min')
                    ->numeric(),
                TextInput::make('salary_range_max')
                    ->numeric(),
                TextInput::make('salary_currency')
                    ->required(),
                Select::make('recruiter_id')
                    ->relationship('recruiter', 'name')
                    ->required(),
                Select::make('created_by_id')
                    ->relationship('createdBy', 'name')
                    ->required(),
                Select::make('status')
                    ->options(RequisitionStatusEnum::class)
                    ->required(),
                Select::make('priority')
                    ->options(RequisitionPriorityEnum::class)
                    ->required(),
                DateTimePicker::make('target_start_at'),
                DateTimePicker::make('approved_at'),
                DateTimePicker::make('published_at'),
                DateTimePicker::make('closed_at'),
                Toggle::make('is_internal_only')
                    ->required(),
                Toggle::make('is_confidential')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
            ]);
    }
}
