<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiThreads;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
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
use He4rt\Admin\Filament\Resources\Ai\AiThreads\Pages\CreateAiThreads;
use He4rt\Admin\Filament\Resources\Ai\AiThreads\Pages\EditAiThreads;
use He4rt\Admin\Filament\Resources\Ai\AiThreads\Pages\ListAiThreads;
use He4rt\Admin\Filament\Resources\Ai\AiThreads\RelationManagers\MessagesRelationManager;
use He4rt\Admin\Filament\Resources\Ai\AiThreads\RelationManagers\UsersRelationManager;
use He4rt\Ai\Models\AiThread;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class AiThreadsResource extends Resource
{
    protected static ?string $model = AiThread::class;

    protected static ?string $slug = 'ai-threads';

    protected static ?string $cluster = ArtificialIntelligenceCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChatBubbleLeftRight;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('ai::filament.resource.ai_thread.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('ai::filament.resource.ai_thread.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ai::filament.resource.ai_thread.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('thread_id'),

                Select::make('assistant_id')
                    ->relationship('assistant', 'name')
                    ->searchable()
                    ->required(),

                Select::make('folder_id')
                    ->relationship('folder', 'name')
                    ->searchable(),

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),

                TextInput::make('name'),

                TextInput::make('cloned_count')
                    ->required()
                    ->integer(),

                TextInput::make('emailed_count')
                    ->required()
                    ->integer(),

                DatePicker::make('saved_at')
                    ->label(__('ai::filament.fields.saved_at')),

                DatePicker::make('locked_at')
                    ->label(__('ai::filament.fields.locked_at')),

                TextEntry::make('created_at')
                    ->label(__('ai::filament.fields.created_at'))
                    ->state(fn (?AiThread $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                TextEntry::make('updated_at')
                    ->label(__('ai::filament.fields.updated_at'))
                    ->state(fn (?AiThread $record): string => $record?->updated_at?->diffForHumans() ?? '-'),

                Checkbox::make('registerMediaConversionsUsingModelInstance'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('thread_id'),

                TextColumn::make('assistant.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('folder.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cloned_count'),

                TextColumn::make('emailed_count'),

                TextColumn::make('saved_at')
                    ->label(__('ai::filament.fields.saved_at'))
                    ->date(),

                TextColumn::make('locked_at')
                    ->label(__('ai::filament.fields.locked_at'))
                    ->date(),

                TextColumn::make('registerMediaConversionsUsingModelInstance'),
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

    public static function getRelations(): array
    {
        return [
            MessagesRelationManager::class,
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiThreads::route('/'),
            'create' => CreateAiThreads::route('/create'),
            'edit' => EditAiThreads::route('/{record}/edit'),
        ];
    }

    /**
     * @return Builder<AiThread>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    /**
     * @return Builder<AiThread>
     */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['assistant', 'folder', 'user']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'assistant.name', 'folder.name', 'user.name'];
    }
}
