<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\Prompts\Pages;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Ai\Prompts\PromptResource;
use He4rt\Admin\Filament\Resources\Ai\PromptTypes\PromptTypeResource;
use He4rt\Ai\Models\Prompt;

final class ListPrompts extends ListRecords
{
    protected static string $resource = PromptResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('type.title')
                    ->sortable()
                    ->url(fn (Prompt $record) => PromptTypeResource::getUrl('view', ['record' => $record->type])),
                TextColumn::make('is_smart')
                    ->label('Kind')
                    ->state(fn (Prompt $record): string => $record->is_smart ? 'Smart' : 'Custom'),
            ])
            ->filters([
                TernaryFilter::make('is_smart')
                    ->label('Kind')
                    ->trueLabel('Smart')
                    ->falseLabel('Custom'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(auth()->check()),
                ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
