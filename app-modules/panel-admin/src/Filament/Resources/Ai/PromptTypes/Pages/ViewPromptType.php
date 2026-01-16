<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\PromptTypes\Pages;

use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use He4rt\Admin\Filament\Resources\Ai\PromptTypes\PromptTypeResource;

final class ViewPromptType extends ViewRecord
{
    protected static string $resource = PromptTypeResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->columns()
                    ->schema([
                        TextEntry::make('title')
                            ->columnSpanFull(),
                        TextEntry::make('description')
                            ->columnSpanFull(),
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
