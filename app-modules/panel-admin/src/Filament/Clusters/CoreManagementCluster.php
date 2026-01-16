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

    protected static ?string $navigationLabel = 'Core Management';

    protected static ?int $navigationSort = 30;

    protected static ?string $clusterBreadcrumb = 'Admin';
}
