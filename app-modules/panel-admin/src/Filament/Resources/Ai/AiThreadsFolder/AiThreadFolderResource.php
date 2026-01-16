<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiThreadsFolder;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
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
use He4rt\Admin\Filament\Resources\Ai\AiThreadsFolder\Pages\CreateAiThreadFolder;
use He4rt\Admin\Filament\Resources\Ai\AiThreadsFolder\Pages\EditAiThreadFolder;
use He4rt\Admin\Filament\Resources\Ai\AiThreadsFolder\Pages\ListAiThreadFolders;
use He4rt\Admin\Filament\Resources\Ai\AiThreadsFolder\RelationManagers\ThreadsRelationManager;
use He4rt\Ai\Enums\AiAssistantApplication;
use He4rt\Ai\Models\AiThreadFolder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class AiThreadFolderResource extends Resource
{
    protected static ?string $model = AiThreadFolder::class;

    protected static ?string $slug = 'ai-thread-folders';

    protected static ?string $cluster = ArtificialIntelligenceCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Folder;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('ai::filament.resource.ai_thread_folder.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('ai::filament.resource.ai_thread_folder.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ai::filament.resource.ai_thread_folder.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),

                Select::make('application')
                    ->options(AiAssistantApplication::class)
                    ->required(),

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),

                TextEntry::make('created_at')
                    ->label(__('ai::filament.fields.created_at'))
                    ->state(fn (?AiThreadFolder $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                TextEntry::make('updated_at')
                    ->label(__('ai::filament.fields.updated_at'))
                    ->state(fn (?AiThreadFolder $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('application'),

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

    public static function getRelations(): array
    {
        return [
            ThreadsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiThreadFolders::route('/'),
            'create' => CreateAiThreadFolder::route('/create'),
            'edit' => EditAiThreadFolder::route('/{record}/edit'),
        ];
    }

    /**
     * @return Builder<AiThreadFolder>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    /**
     * @return Builder<AiThreadFolder>
     */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'user.name'];
    }
}
