<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiAssistants\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Ai\AiAssistants\AiAssistantResource;
use Illuminate\Database\Eloquent\Builder;

final class ListAiAssistants extends ListRecords
{
    protected static string $resource = AiAssistantResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->collection('avatar')
                    ->visibility('private')
                    ->circular(),
                TextColumn::make('name')
                    ->searchable()
                    ->label('Name'),
                IconColumn::make('archived_at')
                    ->label('Archived')
                    ->boolean()
                    ->hidden(fn (Table $table) => $table->getFilter('withoutArchived')->getState()['isActive'] ?? false),
            ])
            ->filters([
                Filter::make('withoutArchived')
                    ->query(fn (Builder $query) => $query->whereNull('archived_at'))
                    ->default(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->emptyStateHeading('No AI Assistants')
            ->emptyStateDescription('Add a new custom AI Assistant by clicking the "Create AI Assistant" button above.');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
