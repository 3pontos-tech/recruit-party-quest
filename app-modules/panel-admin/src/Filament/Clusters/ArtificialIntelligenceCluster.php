<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

final class ArtificialIntelligenceCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::CpuChip;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationLabel = null;

    protected static ?int $navigationSort = 20;

    protected static ?string $clusterBreadcrumb = null;

    public static function getNavigationLabel(): string
    {
        return __('ai::filament.cluster.navigation_label');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('ai::filament.cluster.breadcrumb');
    }
}
