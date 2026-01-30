<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use He4rt\Applications\Models\Application;
use He4rt\Feedback\Actions\StoreCommentAction;
use He4rt\Feedback\DTOs\CommentDTO;

class CommentApplicationAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('panel-organization::filament.actions.add_comment.label'))
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->color('gray')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->outlined()
            ->modalHeading(__('panel-organization::filament.actions.add_comment.modal_heading'))
            ->modalDescription(__('panel-organization::filament.actions.add_comment.modal_description'))
            ->schema($this->formSchema())
            ->action(function (array $data, Application $record): void {
                resolve(StoreCommentAction::class)->execute(
                    CommentDTO::make([
                        'team_id' => filament()->getTenant()->getKey(),
                        'application_id' => $record->getKey(),
                        'author_id' => auth()->user()->getKey(),
                        'content' => $data['content'],
                        'is_internal' => true,
                    ])
                );
                Notification::make()
                    ->title(__('panel-organization::filament.notifications.ok_title'))
                    ->body(__('panel-organization::filament.notifications.ok_body'))
                    ->success()
                    ->send();
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'comment_application-action';
    }

    private function formSchema(): array
    {
        return [
            Textarea::make('content')
                ->label(__('applications::filament.fields.comment'))
                ->rows(3)
                ->required()
                ->maxLength(255)
                ->minLength(3),
        ];
    }
}
