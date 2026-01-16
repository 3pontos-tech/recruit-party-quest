<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiAssistants;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use He4rt\Admin\Filament\Clusters\ArtificialIntelligenceCluster;
use He4rt\Admin\Filament\Resources\Ai\AiAssistants\Pages\CreateAiAssistant;
use He4rt\Admin\Filament\Resources\Ai\AiAssistants\Pages\EditAiAssistant;
use He4rt\Admin\Filament\Resources\Ai\AiAssistants\Pages\ListAiAssistants;
use He4rt\Ai\Models\AiAssistant;

final class AiAssistantResource extends Resource
{
    protected static ?string $model = AiAssistant::class;

    protected static ?string $cluster = ArtificialIntelligenceCluster::class;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?int $navigationSort = 1;

    protected static string|null|BackedEnum $navigationIcon = Heroicon::CpuChip;

    public static function getNavigationLabel(): string
    {
        return __('ai::filament.resource.ai_assistant.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('ai::filament.resource.ai_assistant.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ai::filament.resource.ai_assistant.plural_label');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiAssistants::route('/'),
            'create' => CreateAiAssistant::route('/create'),
            'edit' => EditAiAssistant::route('/{record}/edit'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            EditAiAssistant::class,

        ]);
    }
}
