<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Resources\JobRequisitions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

class JobRequisitionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('team.name')
                    ->label('Team'),
                TextEntry::make('department.name')
                    ->label('Department'),
                TextEntry::make('work_arrangement')
                    ->badge(),
                TextEntry::make('employment_type')
                    ->badge(),
                TextEntry::make('experience_level')
                    ->badge(),
                TextEntry::make('positions_available'),
                TextEntry::make('salary_range_min')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('salary_range_max')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('salary_currency'),
                TextEntry::make('hiringManager.name')
                    ->label('Hiring manager'),
                TextEntry::make('createdBy.name')
                    ->label('Created by'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('priority')
                    ->badge(),
                TextEntry::make('target_start_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('approved_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('published_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('closed_at')
                    ->dateTime()
                    ->placeholder('-'),
                IconEntry::make('is_internal_only')
                    ->boolean(),
                IconEntry::make('is_confidential')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (JobRequisition $record): bool => $record->trashed()),
                TextEntry::make('slug'),
            ]);
    }
}
