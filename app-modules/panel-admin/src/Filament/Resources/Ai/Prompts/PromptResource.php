<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\Prompts;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use He4rt\Admin\Filament\Clusters\ArtificialIntelligenceCluster;
use He4rt\Admin\Filament\Resources\Ai\Prompts\Pages\CreatePrompt;
use He4rt\Admin\Filament\Resources\Ai\Prompts\Pages\EditPrompt;
use He4rt\Admin\Filament\Resources\Ai\Prompts\Pages\ListPrompts;
use He4rt\Admin\Filament\Resources\Ai\Prompts\Pages\ViewPrompt;
use He4rt\Ai\Models\Prompt;

final class PromptResource extends Resource
{
    protected static ?string $model = Prompt::class;

    protected static ?string $cluster = ArtificialIntelligenceCluster::class;

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static ?string $navigationLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('ai::filament.resource.prompt.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('ai::filament.resource.prompt.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ai::filament.resource.prompt.plural_label');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrompts::route('/'),
            'create' => CreatePrompt::route('/create'),
            'view' => ViewPrompt::route('/{record}'),
            'edit' => EditPrompt::route('/{record}/edit'),
        ];
    }
}
