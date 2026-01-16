<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\PromptTypes\Pages;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Ai\PromptTypes\PromptTypeResource;
use He4rt\Ai\Models\PromptType;

final class ListPromptTypes extends ListRecords
{
    protected static string $resource = PromptTypeResource::class;

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
                TextColumn::make('prompts_count')
                    ->label('# of Prompts')
                    ->counts('prompts')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records): void {
                            $deletedPromptTypesCount = PromptType::query()
                                ->whereKey($records)
                                ->whereDoesntHave('prompts')
                                ->delete();

                            Notification::make()
                                ->title('Deleted '.$deletedPromptTypesCount.' prompt types')
                                ->body(($deletedPromptTypesCount < $records->count()) ? ($records->count() - $deletedPromptTypesCount).' prompt types were not deleted because they have prompts.' : null)
                                ->success()
                                ->send();
                        })
                        ->fetchSelectedRecords(false),
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
