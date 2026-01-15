<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Teams\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Users\UserResource;
use Illuminate\Database\Eloquent\Model;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $relatedResource = UserResource::class;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('teams::filament.relation_managers.members.title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                DetachAction::make(),
            ])
            ->headerActions([
                CreateAction::make(),
                AttachAction::make()
                    ->preloadRecordSelect(),
            ]);
    }

    protected static function getModelLabel(): ?string
    {
        return __('teams::filament.relation_managers.members.label');
    }
}
