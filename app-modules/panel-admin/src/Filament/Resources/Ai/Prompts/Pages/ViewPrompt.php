<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\Prompts\Pages;

use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use He4rt\Admin\Filament\Resources\Ai\Prompts\PromptResource;
use He4rt\Admin\Filament\Resources\Ai\PromptTypes\PromptTypeResource;
use He4rt\Ai\Models\Prompt;

final class ViewPrompt extends ViewRecord
{
    protected static string $resource = PromptResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->columns()
                    ->schema([
                        TextEntry::make('title'),
                        TextEntry::make('type.title')
                            ->url(fn (Prompt $record) => PromptTypeResource::getUrl('view', ['record' => $record->type])),
                        TextEntry::make('description')
                            ->columnSpanFull(),
                        TextEntry::make('prompt')
                            ->columnSpanFull()
                            ->visible(fn (Prompt $record): bool => ! $record->is_smart || auth()->check()),
                        TextEntry::make('is_smart')
                            ->label('Kind')
                            ->state(fn (Prompt $record): string => $record->is_smart ? 'Smart' : 'Custom'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {

        return [
            EditAction::make(),
        ];
    }
}
