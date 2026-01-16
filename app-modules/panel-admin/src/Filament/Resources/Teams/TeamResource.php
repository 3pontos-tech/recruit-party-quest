<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Teams;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Clusters\CoreManagementCluster;
use He4rt\Admin\Filament\Resources\Teams\Pages\CreateTeam;
use He4rt\Admin\Filament\Resources\Teams\Pages\EditTeam;
use He4rt\Admin\Filament\Resources\Teams\Pages\ListTeams;
use He4rt\Admin\Filament\Resources\Teams\RelationManagers\MembersRelationManager;
use He4rt\Admin\Filament\Resources\Teams\Schemas\TeamForm;
use He4rt\Admin\Filament\Resources\Teams\Tables\TeamsTable;
use He4rt\Teams\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Override;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $cluster = CoreManagementCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getLabel(): ?string
    {
        return __('teams::filament.resource.label');
    }

    public static function getPluralLabel(): ?string
    {
        return __('teams::filament.resource.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('teams::filament.resource.navigation_label');
    }

    public static function form(Schema $schema): Schema
    {
        return TeamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeamsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeams::route('/'),
            'create' => CreateTeam::route('/create'),
            'edit' => EditTeam::route('/{record}/edit'),
        ];
    }

    /**
     * @return Builder<Team>
     */
    #[Override]
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
