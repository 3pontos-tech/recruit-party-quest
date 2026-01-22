<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Permissions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RoleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->label(__('permissions::filament.fields.created_at'))
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label(__('permissions::filament.fields.updated_at'))
                    ->dateTime(),

                TextEntry::make('guard_name'),

                TextEntry::make('id'),

                TextEntry::make('name'),
            ]);
    }
}
