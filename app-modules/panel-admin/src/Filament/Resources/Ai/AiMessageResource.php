<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Clusters\ArtificialIntelligenceCluster;
use He4rt\Admin\Filament\Resources\Ai\AiMessageResource\Pages\CreateAiMessage;
use He4rt\Admin\Filament\Resources\Ai\AiMessageResource\Pages\EditAiMessage;
use He4rt\Admin\Filament\Resources\Ai\AiMessageResource\Pages\ListAiMessages;
use He4rt\Ai\Models\AiMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class AiMessageResource extends Resource
{
    protected static ?string $model = AiMessage::class;

    protected static ?string $slug = 'ai-messages';

    protected static ?string $cluster = ArtificialIntelligenceCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::EnvelopeOpen;

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('ai::filament.resource.ai_message.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('ai::filament.resource.ai_message.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ai::filament.resource.ai_message.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('prompt_id')
                    ->relationship('prompt', 'title')
                    ->searchable(),

                TextInput::make('message_id'),

                MarkdownEditor::make('content')
                    ->required(),

                TextInput::make('request'),

                Select::make('thread_id')
                    ->relationship('thread', 'name')
                    ->searchable()
                    ->required(),

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable(),

                TextEntry::make('created_at')
                    ->label(__('ai::filament.fields.created_at'))
                    ->state(fn (?AiMessage $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                TextEntry::make('updated_at')
                    ->label(__('ai::filament.fields.updated_at'))
                    ->state(fn (?AiMessage $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('prompt.title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('message_id'),

                TextColumn::make('request'),

                TextColumn::make('thread.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiMessages::route('/'),
            'create' => CreateAiMessage::route('/create'),
            'edit' => EditAiMessage::route('/{record}/edit'),
        ];
    }

    /**
     * @return Builder<AiMessage>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    /**
     * @return Builder<AiMessage>
     */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['prompt', 'thread', 'user']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['prompt.title', 'thread.name', 'user.name'];
    }
}
