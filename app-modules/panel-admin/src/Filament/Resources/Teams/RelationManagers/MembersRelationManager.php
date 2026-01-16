<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Teams\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Users\UserResource;
use He4rt\Teams\Actions\NewMember\InviteTeamMemberAction;
use He4rt\Teams\Actions\NewMember\InviteTeamMemberDTO;
use He4rt\Teams\Team;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Team getOwnerRecord()
 */
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
                // Invite new member via modal form
                Action::make('invite')
                    ->label(__('teams::filament.relation_managers.members.invite_action'))
                    ->modalHeading(__('teams::filament.relation_managers.members.invite_heading'))
                    ->modalDescription(__('teams::filament.relation_managers.members.invite_description'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('teams::filament.fields.name'))
                            ->required(),
                        TextInput::make('email')
                            ->label(__('teams::filament.fields.email'))
                            ->email()
                            ->required(),
                        TextInput::make('password')
                            ->label(__('teams::filament.fields.password'))
                            ->password()
                            ->required(),
                    ])
                    ->mutateDataUsing(function (array $data): array {
                        $data['team_id'] = $this->getOwnerRecord()->getKey();

                        return $data;
                    })
                    ->action(function (array $data): void {

                        resolve(InviteTeamMemberAction::class)->handle(
                            InviteTeamMemberDTO::fromArray($data)
                        );

                        Notification::make()
                            ->title(__('teams::filament.relation_managers.members.invite_success'))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    protected static function getModelLabel(): ?string
    {
        return __('teams::filament.relation_managers.members.label');
    }
}
