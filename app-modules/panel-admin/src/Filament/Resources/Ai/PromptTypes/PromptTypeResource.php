<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\PromptTypes;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use He4rt\Admin\Filament\Clusters\ArtificialIntelligenceCluster;
use He4rt\Admin\Filament\Resources\Ai\PromptTypes\Pages\CreatePromptType;
use He4rt\Admin\Filament\Resources\Ai\PromptTypes\Pages\EditPromptType;
use He4rt\Admin\Filament\Resources\Ai\PromptTypes\Pages\ListPromptTypes;
use He4rt\Admin\Filament\Resources\Ai\PromptTypes\Pages\ViewPromptType;
use He4rt\Admin\Filament\Resources\Ai\PromptTypes\RelationManagers\PromptsRelationManager;
use He4rt\Ai\Models\PromptType;

final class PromptTypeResource extends Resource
{
    protected static ?string $model = PromptType::class;

    protected static ?string $cluster = ArtificialIntelligenceCluster::class;

    protected static ?int $navigationSort = 6;

    protected static string|null|BackedEnum $navigationIcon = Heroicon::Tag;

    public static function getNavigationLabel(): string
    {
        return __('ai::filament.resource.prompt_type.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('ai::filament.resource.prompt_type.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ai::filament.resource.prompt_type.plural_label');
    }

    public static function getRelations(): array
    {
        return [
            PromptsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromptTypes::route('/'),
            'create' => CreatePromptType::route('/create'),
            'view' => ViewPromptType::route('/{record}'),
            'edit' => EditPromptType::route('/{record}/edit'),
        ];
    }
}
