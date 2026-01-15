<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label(__('users::labels.id')),

                TextEntry::make('name')
                    ->label(__('users::labels.name')),

                TextEntry::make('email')
                    ->label(__('users::labels.email')),

                TextEntry::make('email_verified_at')
                    ->label(__('users::labels.email_verified_at'))
                    ->dateTime(),

                TextEntry::make('created_at')
                    ->label(__('users::labels.created_at'))
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label(__('users::labels.updated_at'))
                    ->dateTime(),
            ]);
    }
}
