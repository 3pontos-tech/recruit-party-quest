<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\PromptTypes\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Ai\Prompts\Pages\EditPrompt;
use He4rt\Admin\Filament\Resources\Ai\Prompts\Pages\ListPrompts;
use He4rt\Admin\Filament\Resources\Ai\Prompts\Pages\ViewPrompt;
use He4rt\Admin\Filament\Resources\Ai\Prompts\PromptResource;
use He4rt\Ai\Models\Prompt;

final class PromptsRelationManager extends RelationManager
{
    protected static string $relationship = 'prompts';

    public function infolist(Schema $schema): Schema
    {
        return (new ViewPrompt)->infolist($schema);
    }

    public function form(Schema $schema): Schema
    {
        return (new EditPrompt)->form($schema);
    }

    public function table(Table $table): Table
    {
        return (new ListPrompts)
            ->table($table)
            ->recordTitleAttribute('title')
            ->inverseRelationship('type')
            ->headerActions([
                CreateAction::make()
                    ->url(fn (): string => PromptResource::getUrl('create')),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Prompt $record): string => PromptResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->url(fn (Prompt $record): string => PromptResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make(),
            ]);
    }
}
