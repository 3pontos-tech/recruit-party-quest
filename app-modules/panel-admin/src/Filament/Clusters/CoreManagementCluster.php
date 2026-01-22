<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

final class CoreManagementCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice2;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 30;

    protected static ?string $clusterBreadcrumb = null;

    public static function getNavigationLabel(): string
    {
        return __('panel-admin::filament.cluster.core_management.navigation_label');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('panel-admin::filament.cluster.core_management.breadcrumb');
    }
}
