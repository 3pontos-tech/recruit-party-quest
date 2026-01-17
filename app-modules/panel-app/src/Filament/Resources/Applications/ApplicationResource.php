<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Resources\Applications;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\App\Filament\Resources\Applications\Pages\CreateApplication;
use He4rt\App\Filament\Resources\Applications\Pages\ListApplications;
use He4rt\App\Filament\Resources\Applications\Pages\ViewApplication;
use He4rt\App\Filament\Resources\Applications\Tables\ApplicationsTable;
use He4rt\Applications\Models\Application;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
            'create' => CreateApplication::route('/create'),
            'view' => ViewApplication::route('/{record}'),
        ];
    }
}
